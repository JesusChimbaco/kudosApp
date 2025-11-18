<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { CheckSquare, TrendingUp, Calendar, Target, Flame, Star, Check } from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Interfaces
interface Objetivo {
    id: number;
    user_id: number;
    nombre: string;
    descripcion: string;
    emoji?: string;
    color?: string;
    tipo: string;
    fecha_inicio: string;
    fecha_objetivo?: string;
    completado: boolean;
    fecha_completado?: string;
    activo: boolean;
}

interface Habito {
    id: number;
    categoria_id: number;
    objetivo_id?: number;
    categoria?: any;
    objetivo?: Objetivo;
    nombre: string;
    descripcion: string;
    frecuencia: string;
    hora_preferida?: string;
    objetivo_diario: number;
    fecha_inicio: string;
    fecha_fin?: string;
    dias_semana?: string | number[];
    activo: boolean;
    racha_actual?: number;
    racha_maxima?: number;
}

interface RegistroDiario {
    id: number;
    habito_id: number;
    fecha: string;
    completado: boolean;
    veces_completado: number;
}

interface ProgressStats {
    semanal: { completados: number; total: number; porcentaje: number };
    mensual: { completados: number; total: number; porcentaje: number };
    rachaGlobal: number;
}

// Estado reactivo para datos reales
const habitos = ref<Habito[]>([]);
const objetivos = ref<Objetivo[]>([]);
const registrosHoy = ref<Record<number, RegistroDiario>>({});
const progressStats = ref<ProgressStats>({
    semanal: { completados: 0, total: 0, porcentaje: 0 },
    mensual: { completados: 0, total: 0, porcentaje: 0 },
    rachaGlobal: 0
});
const loading = ref(true);
const habitosActivos = ref(0);
const completadosHoy = ref(0);

// Obtener token CSRF
const getCSRFToken = () => {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
};

// Función para cargar hábitos reales
const fetchHabitos = async () => {
    try {
        loading.value = true;
        
        // Cargar hábitos, objetivos y estadísticas en paralelo
        const [habitosResponse, objetivosResponse, statsResponse] = await Promise.all([
            axios.get('/api/web/habitos', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                },
                withCredentials: true
            }),
            axios.get('/api/web/objetivos', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                },
                withCredentials: true
            }),
            axios.get('/api/web/dashboard/stats', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                },
                withCredentials: true
            })
        ]);

        if (habitosResponse.data.success) {
            habitos.value = habitosResponse.data.data;
            // Contar solo los hábitos que deben mostrarse hoy
            const hoy = new Date();
            habitosActivos.value = habitos.value.filter(h => h.activo && shouldShowHabit(h, hoy)).length;
        }

        if (objetivosResponse.data.success) {
            objetivos.value = objetivosResponse.data.data;
        }

        if (statsResponse.data.success) {
            progressStats.value = statsResponse.data.data;
        }
            
        // Cargar registros de hoy
        await fetchRegistrosHoy();
        
    } catch (error: any) {
        console.error('Error al cargar datos:', error);
    } finally {
        loading.value = false;
    }
};

// Función para cargar los registros de hoy
const fetchRegistrosHoy = async () => {
    const hoy = new Date();
    const fechaHoy = hoy.toISOString().split('T')[0];
    
    // Resetear registros y contador
    registrosHoy.value = {};
    completadosHoy.value = 0;
    
    // Solo procesar hábitos que deben mostrarse hoy
    for (const habito of habitos.value.filter(h => h.activo && shouldShowHabit(h, hoy))) {
        try {
            const response = await axios.get(`/api/web/habitos/${habito.id}/registro/${fechaHoy}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                },
                withCredentials: true
            });

            if (response.data.success && response.data.data) {
                registrosHoy.value[habito.id] = response.data.data;
                if (response.data.data.completado) {
                    completadosHoy.value++;
                }
            }
        } catch (error) {
            // Si no hay registro para hoy, está bien
            console.debug(`No hay registro para hábito ${habito.id}`);
        }
    }
};

// Función para marcar/desmarcar como completado
const toggleCompletado = async (habitoId: number) => {
    const estaCompletado = registrosHoy.value[habitoId]?.completado || false;
    
    console.log('Toggle para hábito:', habitoId, 'Está completado:', estaCompletado);
    
    try {
        const endpoint = estaCompletado 
            ? `/api/web/habitos/${habitoId}/descompletar`
            : `/api/web/habitos/${habitoId}/completar`;

        console.log('Llamando endpoint:', endpoint);

        const response = await axios.post(endpoint, {
            fecha: new Date().toISOString().split('T')[0]
        }, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        console.log('Respuesta:', response.data);

        if (response.data.success) {
            // Actualizar el registro local
            registrosHoy.value[habitoId] = response.data.data.registro || response.data.data;
            
            // Recalcular completados desde cero
            completadosHoy.value = Object.values(registrosHoy.value).filter(r => r.completado).length;
        }
    } catch (error: any) {
        console.error('Error al marcar hábito:', error);
        alert('Error al actualizar el hábito. Por favor, intenta de nuevo.');
    }
};

// Verificar si un hábito está completado hoy
const estaCompletadoHoy = (habitoId: number) => {
    return registrosHoy.value[habitoId]?.completado || false;
};

// Verificar si un hábito debe mostrarse en una fecha específica (misma lógica que el calendario)
const shouldShowHabit = (habito: Habito, date: Date): boolean => {
    const habitStartDate = new Date(habito.fecha_inicio);
    if (date < habitStartDate) return false;
    
    // Verificar si ya pasó la fecha objetivo (si existe)
    if (habito.fecha_fin) {
        const habitEndDate = new Date(habito.fecha_fin);
        if (date > habitEndDate) return false;
    }
    
    const dayOfWeek = date.getDay(); // 0 = domingo, 1 = lunes, etc.
    const dayLetters = ['D', 'L', 'M', 'X', 'J', 'V', 'S'];
    const currentDayLetter = dayLetters[dayOfWeek];
    const habitStartDayOfWeek = habitStartDate.getDay();
    
    switch (habito.frecuencia) {
        case 'diario':
            return true;
        case 'semanal':
            // Para hábitos semanales, mostrar en el mismo día de la semana que se creó
            // O usar dias_semana si está configurado
            if (habito.dias_semana) {
                if (typeof habito.dias_semana === 'string') {
                    return habito.dias_semana.includes(currentDayLetter);
                }
                if (Array.isArray(habito.dias_semana)) {
                    return habito.dias_semana.includes(dayOfWeek);
                }
            }
            // Si no tiene días específicos, mostrar en el mismo día que se creó
            return dayOfWeek === habitStartDayOfWeek;
        case 'mensual':
            // Para hábitos mensuales, mostrar en el mismo día del mes que se creó
            return date.getDate() === habitStartDate.getDate();
        default:
            return false;
    }
};

// Datos con valores reales
const getStats = () => [
    {
        title: 'Hábitos de Hoy',
        value: loading.value ? '...' : `${completadosHoy.value}/${habitosActivos.value}`,
        description: 'Completados hoy',
        icon: CheckSquare,
        trend: loading.value ? 'Cargando...' : `${completadosHoy.value} de ${habitosActivos.value} hábitos`,
        color: 'text-primary'
    },
    {
        title: 'Racha Actual',
        value: loading.value ? '...' : `${progressStats.value.rachaGlobal}`,
        description: 'Días consecutivos',
        icon: Flame,
        trend: loading.value ? 'Cargando...' : `${progressStats.value.rachaGlobal} días de constancia`,
        color: 'text-orange-600'
    },
    {
        title: 'Progreso Mensual',
        value: loading.value ? '...' : `${progressStats.value.mensual.porcentaje}%`,
        description: 'Objetivos completados',
        icon: TrendingUp,
        trend: loading.value ? 'Cargando...' : `${progressStats.value.mensual.completados}/${progressStats.value.mensual.total} objetivos`,
        color: 'text-primary'
    }
];

// Cargar datos al montar el componente
onMounted(() => {
    fetchHabitos();
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Saludo y header -->
            <div class="flex flex-col gap-2">
                <h1 class="text-3xl font-bold text-gradient-kudos">
                    ¡Bienvenido a tu Dashboard!
                </h1>
                <p class="text-muted-foreground">
                    Mantén el seguimiento de tus hábitos y celebra tu progreso diario
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card 
                    v-for="stat in getStats()" 
                    :key="stat.title"
                    class="hover-lift cursor-pointer border-l-4 border-l-primary"
                >
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            {{ stat.title }}
                        </CardTitle>
                        <component :is="stat.icon" :class="`h-4 w-4 ${stat.color}`" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stat.value }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stat.description }}
                        </p>
                        <Badge variant="secondary" class="mt-2 text-xs">
                            {{ stat.trend }}
                        </Badge>
                    </CardContent>
                </Card>
            </div>

            <!-- Main content area -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Hábitos de Hoy -->
                <Card class="hover-lift">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calendar class="h-5 w-5 text-primary" />
                            Hábitos de Hoy
                        </CardTitle>
                        <CardDescription>
                            Completa tus hábitos diarios para mantener tu racha
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="loading" class="text-center py-4">
                            <p class="text-muted-foreground">Cargando hábitos...</p>
                        </div>
                        <div v-else-if="habitos.length === 0" class="text-center py-4">
                            <p class="text-muted-foreground">No tienes hábitos creados aún</p>
                            <Button 
                                variant="outline" 
                                class="mt-2"
                                @click="$inertia.visit('/habitos')"
                            >
                                Crear mi primer hábito
                            </Button>
                        </div>
                        <div v-else class="space-y-3">
                            <div 
                                v-for="habit in habitos.filter(h => h.activo && shouldShowHabit(h, new Date())).slice(0, 4)" 
                                :key="habit.id"
                                class="flex items-start space-x-3 p-3 rounded-lg border hover:bg-accent/50 transition-colors cursor-pointer"
                                @click="toggleCompletado(habit.id)"
                            >
                                <!-- Checkbox -->
                                <div class="flex-shrink-0 mt-0.5">
                                    <div 
                                        :class="[
                                            'h-5 w-5 rounded border-2 flex items-center justify-center transition-all',
                                            estaCompletadoHoy(habit.id) 
                                                ? 'bg-primary border-primary' 
                                                : 'border-muted-foreground/50 hover:border-primary'
                                        ]"
                                    >
                                        <Check 
                                            v-if="estaCompletadoHoy(habit.id)" 
                                            class="h-4 w-4 text-primary-foreground" 
                                        />
                                    </div>
                                </div>
                                
                                <!-- Contenido del hábito -->
                                <div class="flex-1 min-w-0">
                                    <h4 
                                        :class="[
                                            'font-medium',
                                            estaCompletadoHoy(habit.id) ? 'line-through text-muted-foreground' : ''
                                        ]"
                                    >
                                        {{ habit.nombre }}
                                    </h4>
                                    <p 
                                        :class="[
                                            'text-sm',
                                            estaCompletadoHoy(habit.id) ? 'text-muted-foreground/70' : 'text-muted-foreground'
                                        ]"
                                    >
                                        {{ habit.descripcion || 'Sin descripción' }}
                                    </p>
                                    
                                    <!-- Mostrar objetivo si existe -->
                                    <div v-if="habit.objetivo" class="mt-2">
                                        <Badge variant="secondary" class="text-xs">
                                            {{ habit.objetivo.emoji }} {{ habit.objetivo.nombre }}
                                        </Badge>
                                    </div>
                                    
                                    <!-- Mostrar racha si existe -->
                                    <div v-if="habit.racha_actual && habit.racha_actual > 0" class="flex items-center mt-1 space-x-2">
                                        <div class="flex items-center text-xs text-orange-600">
                                            <Flame class="h-3 w-3 mr-1" />
                                            <span>{{ habit.racha_actual }} días</span>
                                        </div>
                                        <div v-if="habit.racha_maxima && habit.racha_maxima > habit.racha_actual" class="flex items-center text-xs text-muted-foreground">
                                            <Star class="h-3 w-3 mr-1" />
                                            <span>Máx: {{ habit.racha_maxima }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Progreso Semanal -->
                <Card class="hover-lift">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Target class="h-5 w-5 text-secondary" />
                            Progreso Semanal
                        </CardTitle>
                        <CardDescription>
                            Tu rendimiento durante los últimos 7 días
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <!-- Progress bar semanal -->
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Progreso Semanal</span>
                                    <span class="font-medium">
                                        {{ loading ? '...' : `${progressStats.semanal.porcentaje}%` }}
                                    </span>
                                </div>
                                <div class="w-full bg-muted rounded-full h-2">
                                    <div 
                                        class="bg-gradient-kudos h-2 rounded-full transition-all duration-500" 
                                        :style="`width: ${progressStats.semanal.porcentaje}%`"
                                    ></div>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{ loading ? 'Cargando...' : `${progressStats.semanal.completados} de ${progressStats.semanal.total} objetivos completados esta semana` }}
                                </p>
                            </div>
                            
                            <!-- Estadísticas adicionales -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex items-center gap-3 p-3 bg-accent rounded-lg">
                                    <Flame class="h-5 w-5 text-orange-500" />
                                    <div>
                                        <p class="font-medium text-sm">{{ progressStats.rachaGlobal }}</p>
                                        <p class="text-xs text-muted-foreground">Días de racha</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-accent rounded-lg">
                                    <Target class="h-5 w-5 text-primary" />
                                    <div>
                                        <p class="font-medium text-sm">{{ objetivos.filter(o => o.activo).length }}</p>
                                        <p class="text-xs text-muted-foreground">Objetivos activos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Call to action -->
            <Card class="bg-gradient-kudos text-white border-none hover-lift">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold mb-2">¿Listo para crear un nuevo hábito?</h3>
                            <p class="text-white/90">
                                Expande tu rutina y construye la mejor versión de ti mismo
                            </p>
                        </div>
                        <Button 
                            variant="secondary" 
                            class="bg-white text-primary hover:bg-white/90"
                            @click="$inertia.visit('/habitos')"
                        >
                            <CheckSquare class="h-4 w-4 mr-2" />
                            Crear hábito
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
