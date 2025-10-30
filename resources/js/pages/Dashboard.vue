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
interface Habito {
    id: number;
    categoria_id: number;
    categoria?: any;
    nombre: string;
    descripcion: string;
    frecuencia: string;
    hora_preferida: string;
    objetivo_diario: number;
    fecha_inicio: string;
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

// Estado reactivo para datos reales
const habitos = ref<Habito[]>([]);
const registrosHoy = ref<Record<number, RegistroDiario>>({});
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
        
        const response = await axios.get('/api/web/habitos', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            habitos.value = response.data.data;
            // Contar hábitos activos
            habitosActivos.value = habitos.value.filter(h => h.activo).length;
            
            // Cargar registros de hoy para cada hábito
            await fetchRegistrosHoy();
        }
    } catch (error: any) {
        console.error('Error al cargar hábitos:', error);
    } finally {
        loading.value = false;
    }
};

// Función para cargar los registros de hoy
const fetchRegistrosHoy = async () => {
    const hoy = new Date().toISOString().split('T')[0];
    
    // Resetear registros y contador
    registrosHoy.value = {};
    completadosHoy.value = 0;
    
    for (const habito of habitos.value.filter(h => h.activo)) {
        try {
            const response = await axios.get(`/api/web/habitos/${habito.id}/registro/${hoy}`, {
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
    
    try {
        const endpoint = estaCompletado 
            ? `/api/web/habitos/${habitoId}/descompletar`
            : `/api/web/habitos/${habitoId}/completar`;

        const response = await axios.post(endpoint, {
            fecha: new Date().toISOString().split('T')[0]
        }, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            // Actualizar el registro local
            registrosHoy.value[habitoId] = response.data.data;
            
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

// Datos con valores reales y placeholders
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
        value: 'X',
        description: 'Días consecutivos',
        icon: Flame,
        trend: 'Próximamente',
        color: 'text-secondary'
    },
    {
        title: 'Progreso Mensual',
        value: 'X',
        description: 'Objetivos completados',
        icon: TrendingUp,
        trend: 'Próximamente',
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
                                v-for="habit in habitos.filter(h => h.activo).slice(0, 4)" 
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
                            <!-- Progress bar placeholder -->
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Completado</span>
                                    <span class="font-medium">Próximamente</span>
                                </div>
                                <div class="w-full bg-muted rounded-full h-2">
                                    <div class="bg-gradient-to-r from-muted-foreground to-muted-foreground h-2 rounded-full" style="width: 0%"></div>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    El seguimiento de progreso estará disponible próximamente
                                </p>
                            </div>
                            
                            <!-- Placeholder para logros -->
                            <div class="flex items-center gap-3 p-3 bg-accent rounded-lg">
                                <Star class="h-5 w-5 text-yellow-500" />
                                <div>
                                    <p class="font-medium text-sm">Sistema de logros</p>
                                    <p class="text-xs text-muted-foreground">Próximamente disponible</p>
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
                            Crear Hábito
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
