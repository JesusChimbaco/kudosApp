<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import { Checkbox } from '@/components/ui/checkbox';

import { 
    ArrowLeft, Plus, Target, Calendar, CheckCircle, Clock, Trash2, Edit, 
    CheckSquare, TrendingUp, Flame, Star
} from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Props
interface Props {
    objetivoId: number;
}
const props = defineProps<Props>();

// Breadcrumbs dinámicos
const breadcrumbs = ref<BreadcrumbItem[]>([
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Objetivos',
        href: '/objetivos',
    },
    {
        title: 'Cargando...',
        href: '#',
    },
]);

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

interface Categoria {
    id: number;
    nombre: string;
    descripcion: string;
    color: string;
    icono: string;
}

interface Habito {
    id: number;
    categoria_id: number;
    objetivo_id?: number;
    categoria?: Categoria;
    objetivo?: Objetivo;
    nombre: string;
    descripcion: string;
    frecuencia: string;
    hora_preferida?: string;
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

// Función para formatear fecha local (evita conversión UTC)
const formatDateLocal = (date: Date = new Date()): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Estado reactivo
const objetivo = ref<Objetivo | null>(null);
const habitos = ref<Habito[]>([]);
const categorias = ref<Categoria[]>([]);
const registrosHoy = ref<Record<number, RegistroDiario>>({});
const loading = ref(true);
const showCreateDialog = ref(false);

// Formulario de hábito
const habitoForm = ref({
    nombre: '',
    descripcion: '',
    categoria_id: null as number | null,
    frecuencia: 'diario',
    fecha_inicio: formatDateLocal(new Date()),
    fecha_fin: '',
    dias_semana: [] as number[],
});

const frecuenciasDisponibles = [
    { value: 'diario', label: 'Diario' },
    { value: 'semanal', label: 'Semanal' },
    { value: 'mensual', label: 'Mensual' },
];

const diasSemana = [
    { value: 1, label: 'Lunes', short: 'L' },
    { value: 2, label: 'Martes', short: 'M' },
    { value: 3, label: 'Miércoles', short: 'X' },
    { value: 4, label: 'Jueves', short: 'J' },
    { value: 5, label: 'Viernes', short: 'V' },
    { value: 6, label: 'Sábado', short: 'S' },
    { value: 0, label: 'Domingo', short: 'D' },
];

// Obtener token CSRF
const getCSRFToken = () => {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
};

// Cargar objetivo
const fetchObjetivo = async () => {
    try {
        const response = await axios.get(`/api/web/objetivos/${props.objetivoId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            objetivo.value = response.data.data;
            // Actualizar breadcrumb
            if (objetivo.value) {
                breadcrumbs.value[2] = {
                    title: objetivo.value.nombre,
                    href: `/objetivos/${objetivo.value.id}`,
                };
            }
        }
    } catch (error: any) {
        console.error('Error al cargar objetivo:', error);
    }
};

// Cargar hábitos del objetivo
const fetchHabitos = async () => {
    try {
        const response = await axios.get('/api/web/habitos', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            // Filtrar solo los hábitos de este objetivo
            habitos.value = response.data.data.filter((h: Habito) => h.objetivo_id === props.objetivoId);
        }
    } catch (error: any) {
        console.error('Error al cargar hábitos:', error);
    }
};

// Cargar categorías
const fetchCategorias = async () => {
    try {
        const response = await axios.get('/api/web/categorias', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            categorias.value = response.data.data;
        }
    } catch (error: any) {
        console.error('Error al cargar categorías:', error);
    }
};

// Cargar registros de hoy
const fetchRegistrosHoy = async () => {
    const hoy = new Date();
    const fechaHoy = formatDateLocal(hoy);
    
    registrosHoy.value = {};
    
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
            }
        } catch (error) {
            console.debug(`No hay registro para hábito ${habito.id}`);
        }
    }
};

// Crear hábito
const crearHabito = async () => {
    try {
        const habitoData = {
            ...habitoForm.value,
            objetivo_id: props.objetivoId,
            // Convertir días_semana a string si es semanal
            dias_semana: habitoForm.value.frecuencia === 'semanal' 
                ? habitoForm.value.dias_semana.map(d => diasSemana.find(ds => ds.value === d)?.short).join('')
                : null
        };

        const response = await axios.post('/api/web/habitos', habitoData, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            await fetchHabitos();
            await fetchRegistrosHoy();
            resetHabitoForm();
            showCreateDialog.value = false;
        }
    } catch (error: any) {
        console.error('Error al crear hábito:', error);
        alert('Error al crear hábito. Por favor, intenta de nuevo.');
    }
};

// Eliminar hábito
const eliminarHabito = async (habito: Habito) => {
    if (!confirm('¿Estás seguro de que quieres eliminar este hábito?')) return;

    try {
        const response = await axios.delete(`/api/web/habitos/${habito.id}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            await fetchHabitos();
            await fetchRegistrosHoy();
        }
    } catch (error: any) {
        console.error('Error al eliminar hábito:', error);
        alert('Error al eliminar hábito. Por favor, intenta de nuevo.');
    }
};

// Toggle completado
const toggleCompletado = async (habitoId: number) => {
    const estaCompletado = registrosHoy.value[habitoId]?.completado || false;
    
    try {
        const endpoint = estaCompletado 
            ? `/api/web/habitos/${habitoId}/descompletar`
            : `/api/web/habitos/${habitoId}/completar`;

        const response = await axios.post(endpoint, {
            fecha: formatDateLocal(new Date())
        }, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            registrosHoy.value[habitoId] = response.data.data.registro || response.data.data;
        }
    } catch (error: any) {
        console.error('Error al marcar hábito:', error);
        alert('Error al actualizar el hábito. Por favor, intenta de nuevo.');
    }
};

// Función para determinar si un hábito debe mostrarse hoy
const shouldShowHabit = (habito: Habito, date: Date): boolean => {
    const habitStartDate = new Date(habito.fecha_inicio);
    if (date < habitStartDate) return false;
    
    if (habito.fecha_fin) {
        const habitEndDate = new Date(habito.fecha_fin);
        if (date > habitEndDate) return false;
    }
    
    const dayOfWeek = date.getDay();
    const dayLetters = ['D', 'L', 'M', 'X', 'J', 'V', 'S'];
    const currentDayLetter = dayLetters[dayOfWeek];
    const habitStartDayOfWeek = habitStartDate.getDay();
    
    switch (habito.frecuencia) {
        case 'diario':
            return true;
        case 'semanal':
            if (habito.dias_semana) {
                if (typeof habito.dias_semana === 'string') {
                    return habito.dias_semana.includes(currentDayLetter);
                }
                if (Array.isArray(habito.dias_semana)) {
                    return habito.dias_semana.includes(dayOfWeek);
                }
            }
            return dayOfWeek === habitStartDayOfWeek;
        case 'mensual':
            return date.getDate() === habitStartDate.getDate();
        default:
            return false;
    }
};

// Resetear formulario
const resetHabitoForm = () => {
    habitoForm.value = {
        nombre: '',
        descripcion: '',
        categoria_id: null,
        frecuencia: 'diario',
        fecha_inicio: formatDateLocal(new Date()),
        fecha_fin: '',
        dias_semana: [],
    };
};

// Toggle día de la semana
const toggleDiaSemana = (dia: number) => {
    const index = habitoForm.value.dias_semana.indexOf(dia);
    if (index > -1) {
        habitoForm.value.dias_semana.splice(index, 1);
    } else {
        habitoForm.value.dias_semana.push(dia);
    }
};

// Computed properties
const habitosHoy = computed(() => {
    const hoy = new Date();
    return habitos.value.filter(h => h.activo && shouldShowHabit(h, hoy));
});

const habitosCompletadosHoy = computed(() => {
    return habitosHoy.value.filter(h => registrosHoy.value[h.id]?.completado);
});

const porcentajeCompletado = computed(() => {
    if (habitosHoy.value.length === 0) return 0;
    return Math.round((habitosCompletadosHoy.value.length / habitosHoy.value.length) * 100);
});

const diasRestantes = computed(() => {
    if (!objetivo.value?.fecha_objetivo) return null;
    const hoy = new Date();
    const fechaObj = new Date(objetivo.value.fecha_objetivo);
    const diferencia = fechaObj.getTime() - hoy.getTime();
    const dias = Math.ceil(diferencia / (1000 * 60 * 60 * 24));
    return dias;
});

// Formatear fecha
const formatearFecha = (fecha: string | null) => {
    if (!fecha) return 'Sin fecha límite';
    return new Date(fecha).toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

// Cargar todos los datos
const cargarDatos = async () => {
    try {
        loading.value = true;
        await Promise.all([
            fetchObjetivo(),
            fetchCategorias(),
        ]);
        await fetchHabitos();
        await fetchRegistrosHoy();
    } catch (error) {
        console.error('Error al cargar datos:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    cargarDatos();
});
</script>

<template>
    <Head :title="objetivo?.nombre || 'Objetivo'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div v-if="loading" class="text-center py-8">
                <p class="text-muted-foreground">Cargando objetivo...</p>
            </div>

            <div v-else-if="!objetivo" class="text-center py-8">
                <p class="text-muted-foreground">Objetivo no encontrado</p>
                <Button @click="$inertia.visit('/objetivos')" variant="outline" class="mt-2">
                    Volver a objetivos
                </Button>
            </div>

            <div v-else class="space-y-6">
                <!-- Header del objetivo -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <Button 
                            variant="ghost" 
                            size="sm" 
                            @click="$inertia.visit('/objetivos')"
                            class="gap-2"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Volver
                        </Button>
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-4xl">{{ objetivo.emoji }}</span>
                                <div>
                                    <h1 class="text-3xl font-bold text-gradient-kudos">{{ objetivo.nombre }}</h1>
                                    <p class="text-muted-foreground">{{ objetivo.descripcion }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <Badge 
                                    :style="{ backgroundColor: objetivo.color + '20', color: objetivo.color }"
                                >
                                    {{ objetivo.tipo }}
                                </Badge>
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Calendar class="h-4 w-4" />
                                    <span>{{ formatearFecha(objetivo.fecha_objetivo || null) }}</span>
                                </div>
                                <div v-if="diasRestantes !== null">
                                    <Badge 
                                        :variant="diasRestantes > 30 ? 'secondary' : diasRestantes > 0 ? 'default' : 'destructive'"
                                    >
                                        <Clock class="h-3 w-3 mr-1" />
                                        {{ diasRestantes > 0 
                                            ? `${diasRestantes} días restantes`
                                            : 'Fecha vencida'
                                        }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Dialog v-model:open="showCreateDialog">
                        <DialogTrigger as-child>
                            <Button class="gap-2">
                                <Plus class="h-4 w-4" />
                                Agregar hábito
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>Crear nuevo hábito</DialogTitle>
                                <DialogDescription>
                                    Agrega un hábito que te ayude a alcanzar este objetivo.
                                </DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="crearHabito" class="space-y-4">
                                <!-- Nombre -->
                                <div class="space-y-2">
                                    <Label for="nombre">Nombre del hábito</Label>
                                    <Input 
                                        id="nombre" 
                                        v-model="habitoForm.nombre" 
                                        placeholder="Ej: Correr 30 minutos"
                                        required
                                    />
                                </div>

                                <!-- Descripción -->
                                <div class="space-y-2">
                                    <Label for="descripcion">Descripción</Label>
                                    <textarea 
                                        id="descripcion" 
                                        v-model="habitoForm.descripcion" 
                                        placeholder="Describe brevemente este hábito..."
                                        rows="2"
                                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    />
                                </div>

                                <!-- Categoría -->
                                <div class="space-y-2">
                                    <Label for="categoria">Categoría</Label>
                                    <select
                                        id="categoria"
                                        v-model="habitoForm.categoria_id"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="" disabled>Selecciona una categoría</option>
                                        <option 
                                            v-for="categoria in categorias" 
                                            :key="categoria.id" 
                                            :value="categoria.id"
                                        >
                                            {{ categoria.nombre }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Frecuencia -->
                                <div class="space-y-2">
                                    <Label for="frecuencia">Frecuencia</Label>
                                    <select
                                        id="frecuencia"
                                        v-model="habitoForm.frecuencia"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="" disabled>Selecciona la frecuencia</option>
                                        <option 
                                            v-for="freq in frecuenciasDisponibles" 
                                            :key="freq.value" 
                                            :value="freq.value"
                                        >
                                            {{ freq.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Días de la semana (solo si es semanal) -->
                                <div v-if="habitoForm.frecuencia === 'semanal'" class="space-y-2">
                                    <Label>Días de la semana</Label>
                                    <div class="flex gap-2">
                                        <button
                                            v-for="dia in diasSemana"
                                            :key="dia.value"
                                            type="button"
                                            @click="toggleDiaSemana(dia.value)"
                                            :class="[
                                                'w-8 h-8 rounded-full border text-sm font-medium',
                                                habitoForm.dias_semana.includes(dia.value)
                                                    ? 'bg-primary text-primary-foreground border-primary'
                                                    : 'border-muted-foreground/20 hover:border-primary'
                                            ]"
                                        >
                                            {{ dia.short }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Fechas -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="fecha_inicio">Fecha de inicio</Label>
                                        <Input 
                                            id="fecha_inicio" 
                                            v-model="habitoForm.fecha_inicio" 
                                            type="date"
                                            required
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="fecha_fin">Fecha fin (opcional)</Label>
                                        <Input 
                                            id="fecha_fin" 
                                            v-model="habitoForm.fecha_fin" 
                                            type="date"
                                        />
                                    </div>
                                </div>

                                <!-- Botones -->
                                <div class="flex gap-2 justify-end">
                                    <Button type="button" variant="outline" @click="showCreateDialog = false">
                                        Cancelar
                                    </Button>
                                    <Button type="submit">
                                        Crear hábito
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <!-- Estadísticas del objetivo -->
                <div class="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Progreso Diario</CardTitle>
                            <Target class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ porcentajeCompletado }}%</div>
                                <div class="w-full bg-muted rounded-full h-2 mt-2">
                                    <div 
                                        class="bg-primary h-2 rounded-full transition-all duration-300" 
                                        :style="`width: ${porcentajeCompletado}%`"
                                    ></div>
                                </div>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ habitosCompletadosHoy.length }} de {{ habitosHoy.length }} hábitos
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Hábitos totales</CardTitle>
                            <CheckSquare class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ habitos.length }}</div>
                            <p class="text-xs text-muted-foreground">
                                {{ habitos.filter(h => h.activo).length }} activos
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Para Hoy</CardTitle>
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ habitosHoy.length }}</div>
                            <p class="text-xs text-muted-foreground">
                                Hábitos programados hoy
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Tiempo Restante</CardTitle>
                            <Clock class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ diasRestantes !== null 
                                    ? (diasRestantes > 0 ? diasRestantes : 0)
                                    : '∞'
                                }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ diasRestantes !== null ? 'días restantes' : 'sin límite' }}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Lista de hábitos -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold">Hábitos de este objetivo</h2>
                    
                    <div v-if="habitos.length === 0" class="text-center py-8 border-2 border-dashed rounded-lg">
                        <CheckSquare class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                        <p class="text-muted-foreground mb-2">No hay hábitos para este objetivo</p>
                        <Button @click="showCreateDialog = true" variant="outline">
                            Crear primer hábito
                        </Button>
                    </div>

                    <div v-else class="grid gap-4 md:grid-cols-2">
                        <Card 
                            v-for="habito in habitos" 
                            :key="habito.id"
                            :class="[
                                'hover-lift transition-all',
                                !habito.activo && 'opacity-50',
                                shouldShowHabit(habito, new Date()) && 'border-l-4 border-l-primary'
                            ]"
                        >
                            <CardHeader>
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-3">
                                        <div 
                                            class="flex-shrink-0 mt-1 cursor-pointer"
                                            @click="shouldShowHabit(habito, new Date()) && toggleCompletado(habito.id)"
                                        >
                                            <div 
                                                :class="[
                                                    'h-5 w-5 rounded border-2 flex items-center justify-center transition-all',
                                                    registrosHoy[habito.id]?.completado
                                                        ? 'bg-primary border-primary' 
                                                        : 'border-muted-foreground/50 hover:border-primary',
                                                    !shouldShowHabit(habito, new Date()) && 'opacity-50 cursor-not-allowed'
                                                ]"
                                            >
                                                <CheckCircle 
                                                    v-if="registrosHoy[habito.id]?.completado" 
                                                    class="h-4 w-4 text-primary-foreground" 
                                                />
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <CardTitle 
                                                :class="[
                                                    'text-base',
                                                    registrosHoy[habito.id]?.completado && 'line-through text-muted-foreground'
                                                ]"
                                            >
                                                {{ habito.nombre }}
                                            </CardTitle>
                                            <CardDescription>
                                                {{ habito.descripcion || 'Sin descripción' }}
                                            </CardDescription>
                                            <div class="flex items-center gap-2 mt-2">
                                                <Badge variant="outline">
                                                    {{ frecuenciasDisponibles.find(f => f.value === habito.frecuencia)?.label }}
                                                </Badge>
                                                <Badge variant="secondary">
                                                    {{ habito.categoria?.nombre }}
                                                </Badge>
                                                <div v-if="!shouldShowHabit(habito, new Date())" class="text-xs text-muted-foreground">
                                                    No programado hoy
                                                </div>
                                            </div>
                                            <div v-if="habito.racha_actual && habito.racha_actual > 0" class="flex items-center mt-2 space-x-2">
                                                <div class="flex items-center text-xs text-orange-600">
                                                    <Flame class="h-3 w-3 mr-1" />
                                                    <span>{{ habito.racha_actual }} días</span>
                                                </div>
                                                <div v-if="habito.racha_maxima && habito.racha_maxima > habito.racha_actual" class="flex items-center text-xs text-muted-foreground">
                                                    <Star class="h-3 w-3 mr-1" />
                                                    <span>Máx: {{ habito.racha_maxima }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <Button size="sm" variant="ghost" @click="eliminarHabito(habito)">
                                            <Trash2 class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.text-gradient-kudos {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hover-lift {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}
</style>