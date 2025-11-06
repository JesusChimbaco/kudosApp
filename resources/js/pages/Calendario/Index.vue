<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Calendar, ChevronLeft, ChevronRight, Clock, Target, Check, X } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';

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
    dias_semana?: string | number[];
    veces_por_semana?: number;
}

interface RegistroDiario {
    id: number;
    habito_id: number;
    fecha: string;
    completado: boolean;
    veces_completado: number;
}

interface Recordatorio {
    id: number;
    habito_id: number;
    hora: string;
    dias_semana: string | null;
    activo: boolean;
}

// Estados de carga
const habitos = ref<Habito[]>([]);
const recordatorios = ref<Recordatorio[]>([]);
const registros = ref<Record<string, RegistroDiario[]>>({});
const loading = ref(true);
const loadingHistorial = ref(false); // Estado para carga de historial
const currentDate = ref(new Date());
const viewMode = ref<'day' | 'week' | 'month'>('month');

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Calendario de Hábitos',
        href: '/calendario',
    },
];

// Obtener token CSRF
const getCSRFToken = () => {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
};

// Funciones para fechas
const formatDate = (date: Date) => {
    return date.toISOString().split('T')[0];
};

const formatDisplayDate = (date: Date) => {
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Formatear hora sin segundos (HH:MM)
const formatHora = (horaCompleta: string) => {
    if (!horaCompleta) return '';
    // Si la hora viene en formato HH:MM:SS, tomar solo HH:MM
    return horaCompleta.substring(0, 5);
};

// Obtener todas las horas de recordatorios para un hábito en una fecha específica
const getHorasRecordatorios = (habitoId: number, date: Date): string[] => {
    const recordatoriosDelHabito = getRecordatoriosForHabit(habitoId, date);
    const horas = recordatoriosDelHabito.map(r => formatHora(r.hora)).filter(Boolean);
    // Eliminar duplicados y ordenar las horas
    return [...new Set(horas)].sort();
};

// Verificar si una fecha es el día actual
const isToday = (date: Date): boolean => {
    return date.toDateString() === new Date().toDateString();
};

const formatDisplayMonth = (date: Date) => {
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long'
    });
};

const formatDisplayWeek = (startDate: Date, endDate: Date) => {
    return `${startDate.getDate()} - ${endDate.getDate()} de ${startDate.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' })}`;
};

// Computed para el título actual
const currentTitle = computed(() => {
    switch (viewMode.value) {
        case 'day':
            return formatDisplayDate(currentDate.value);
        case 'week':
            const weekStart = getWeekStart(currentDate.value);
            const weekEnd = getWeekEnd(currentDate.value);
            return formatDisplayWeek(weekStart, weekEnd);
        case 'month':
            return formatDisplayMonth(currentDate.value);
        default:
            return '';
    }
});

// Navegación de fechas
const navigatePrevious = () => {
    const newDate = new Date(currentDate.value);
    switch (viewMode.value) {
        case 'day':
            newDate.setDate(newDate.getDate() - 1);
            break;
        case 'week':
            newDate.setDate(newDate.getDate() - 7);
            break;
        case 'month':
            newDate.setMonth(newDate.getMonth() - 1);
            break;
    }
    currentDate.value = newDate;
    loadData();
};

const navigateNext = () => {
    const newDate = new Date(currentDate.value);
    switch (viewMode.value) {
        case 'day':
            newDate.setDate(newDate.getDate() + 1);
            break;
        case 'week':
            newDate.setDate(newDate.getDate() + 7);
            break;
        case 'month':
            newDate.setMonth(newDate.getMonth() + 1);
            break;
    }
    currentDate.value = newDate;
    loadData();
};

const goToToday = () => {
    currentDate.value = new Date();
    loadData();
};

// Funciones para obtener inicio y fin de semana
const getWeekStart = (date: Date) => {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Lunes como primer día
    return new Date(d.setDate(diff));
};

const getWeekEnd = (date: Date) => {
    const weekStart = getWeekStart(date);
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 6);
    return weekEnd;
};

// Obtener días del mes actual
const getMonthDays = () => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    
    // Obtener el primer lunes antes o igual al primer día del mes
    const startDate = new Date(firstDay);
    const dayOfWeek = firstDay.getDay();
    const daysToSubtract = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
    startDate.setDate(firstDay.getDate() - daysToSubtract);
    
    // Generar todas las fechas para mostrar (6 semanas)
    const days = [];
    for (let i = 0; i < 42; i++) {
        const day = new Date(startDate);
        day.setDate(startDate.getDate() + i);
        days.push(day);
    }
    
    return days;
};

// Obtener días de la semana actual
const getWeekDays = () => {
    const weekStart = getWeekStart(currentDate.value);
    const days = [];
    for (let i = 0; i < 7; i++) {
        const day = new Date(weekStart);
        day.setDate(weekStart.getDate() + i);
        days.push(day);
    }
    return days;
};

// Verificar si un hábito debe mostrarse en una fecha específica
const shouldShowHabit = (habito: Habito, date: Date): boolean => {
    const habitStartDate = new Date(habito.fecha_inicio);
    if (date < habitStartDate) return false;
    
    const dayOfWeek = date.getDay(); // 0 = domingo, 1 = lunes, etc.
    const dayLetters = ['D', 'L', 'M', 'X', 'J', 'V', 'S'];
    const currentDayLetter = dayLetters[dayOfWeek];
    
    switch (habito.frecuencia) {
        case 'diario':
            return true;
        case 'semanal':
            // Primero verificar recordatorios del hábito
            const habitoRecordatorios = recordatorios.value.filter(r => r.habito_id === habito.id && r.activo);
            
            if (habitoRecordatorios.length > 0) {
                return habitoRecordatorios.some(recordatorio => {
                    return recordatorio.dias_semana && 
                           typeof recordatorio.dias_semana === 'string' && 
                           recordatorio.dias_semana.includes(currentDayLetter);
                });
            }
            
            // Si no hay recordatorios, revisar dias_semana del hábito
            if (habito.dias_semana) {
                if (typeof habito.dias_semana === 'string') {
                    return habito.dias_semana.includes(currentDayLetter);
                }
                if (Array.isArray(habito.dias_semana)) {
                    return habito.dias_semana.includes(dayOfWeek);
                }
            }
            
            return false;
        case 'mensual':
            return date.getDate() === habitStartDate.getDate();
        default:
            return false;
    }
};

// Obtener recordatorios para un hábito en una fecha específica
const getRecordatoriosForHabit = (habitoId: number, date: Date): Recordatorio[] => {
    const dayOfWeek = date.getDay();
    const dayLetters = ['D', 'L', 'M', 'X', 'J', 'V', 'S'];
    const currentDayLetter = dayLetters[dayOfWeek];
    
    return recordatorios.value.filter(recordatorio => {
        if (recordatorio.habito_id !== habitoId || !recordatorio.activo) return false;
        
        // Si no tiene días específicos, se aplica todos los días
        if (!recordatorio.dias_semana) return true;
        
        // Verificar si el día actual está en los días configurados
        return recordatorio.dias_semana.includes(currentDayLetter);
    });
};

// Obtener hábitos para un día específico (optimizado con memoización)
const habitosPorDia = computed(() => {
    const cache = new Map<string, Habito[]>();
    
    return (date: Date): Habito[] => {
        const dateKey = formatDate(date);
        
        if (!cache.has(dateKey)) {
            const filtered = habitos.value.filter(h => shouldShowHabit(h, date));
            cache.set(dateKey, filtered);
        }
        
        return cache.get(dateKey)!;
    };
});

const getHabitosForDay = (date: Date): Habito[] => {
    return habitosPorDia.value(date);
};

// Verificar si un hábito está completado en una fecha (optimizado)
const isHabitoCompleted = (habitoId: number, date: string): boolean => {
    const dayRegistros = registros.value[date] || [];
    const registro = dayRegistros.find(r => r.habito_id === habitoId);
    return registro?.completado || false;
};

// Cargar datos (con indicador de historial)
const loadData = async () => {
    loading.value = true;
    loadingHistorial.value = true;
    
    try {
        // Cargar hábitos primero (crítico)
        await fetchHabitos();
        
        // Cargar recordatorios 
        await fetchRecordatorios();
        
        // La interfaz ya se puede mostrar
        loading.value = false;
        
        // Cargar registros (historial)
        await fetchRegistrosOptimized();
        
    } catch (error) {
        console.error('Error al cargar datos:', error);
        loading.value = false;
    } finally {
        loadingHistorial.value = false;
    }
};

// Función súper optimizada para cargar registros al instante
const fetchRegistrosOptimized = async () => {
    try {
        let startDate: Date, endDate: Date;
        
        switch (viewMode.value) {
            case 'day':
                startDate = endDate = new Date(currentDate.value);
                break;
            case 'week':
                startDate = getWeekStart(currentDate.value);
                endDate = getWeekEnd(currentDate.value);
                break;
            case 'month':
                const monthDays = getMonthDays();
                startDate = monthDays[0];
                endDate = monthDays[monthDays.length - 1];
                break;
        }
        
        // Generar todas las fechas
        const fechas: string[] = [];
        const currentDateLoop = new Date(startDate);
        
        while (currentDateLoop <= endDate) {
            fechas.push(formatDate(currentDateLoop));
            currentDateLoop.setDate(currentDateLoop.getDate() + 1);
        }
        
        // Limpiar registros previos
        registros.value = {};
        
        // Cargar TODOS los registros al mismo tiempo con máxima concurrencia
        // Crear todas las promesas de una vez
        const allPromises: Promise<void>[] = [];
        
        fechas.forEach(fecha => {
            habitos.value.forEach(habito => {
                allPromises.push(
                    axios.get(`/api/web/habitos/${habito.id}/registro/${fecha}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCSRFToken(),
                        },
                        withCredentials: true
                    }).then(response => {
                        if (response.data.success && response.data.data) {
                            if (!registros.value[fecha]) {
                                registros.value[fecha] = [];
                            }
                            registros.value[fecha].push(response.data.data);
                        }
                    }).catch(() => {
                        // Silenciar errores para registros que no existen
                    })
                );
            });
        });
        
        // Ejecutar TODAS las promesas en paralelo total
        await Promise.all(allPromises);
        
    } catch (error) {
        console.error('Error al cargar registros optimizados:', error);
    }
};

// Versión rápida sin logs para cargar registros
const fetchRegistrosForDateFast = async (dateStr: string) => {
    try {
        const promises = habitos.value.map(async (habito) => {
            try {
                const response = await axios.get(`/api/web/habitos/${habito.id}/registro/${dateStr}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken(),
                    },
                    withCredentials: true
                });
                return response.data.success && response.data.data ? response.data.data : null;
            } catch {
                return null; // Silenciar errores para no saturar la consola
            }
        });
        
        const dayRegistros = (await Promise.all(promises)).filter(Boolean);
        if (dayRegistros.length > 0) {
            registros.value[dateStr] = dayRegistros;
        }
    } catch (error) {
        // Silenciar errores para esta versión optimizada
    }
};

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
            habitos.value = response.data.data.filter((h: Habito) => h.activo);
        }
    } catch (error) {
        console.error('Error al cargar hábitos:', error);
    }
};

const fetchRecordatorios = async () => {
    try {
        // Si no hay hábitos, no hay recordatorios que cargar
        if (habitos.value.length === 0) {
            recordatorios.value = [];
            return;
        }
        
        // Obtener recordatorios para todos los hábitos en paralelo (optimizado)
        const recordatoriosPromises = habitos.value.map(async (habito) => {
            try {
                const response = await axios.get(`/api/web/habitos/${habito.id}/recordatorios`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken(),
                    },
                    withCredentials: true
                });
                return response.data.success ? response.data.data : [];
            } catch (error) {
                console.warn(`Error cargando recordatorios del hábito ${habito.id}:`, error);
                return [];
            }
        });
        
        const allRecordatorios = await Promise.all(recordatoriosPromises);
        recordatorios.value = allRecordatorios.flat().filter((r: Recordatorio) => r.activo);
    } catch (error) {
        console.error('Error al cargar recordatorios:', error);
    }
};

const fetchRegistros = async () => {
    try {
        let startDate: Date, endDate: Date;
        
        switch (viewMode.value) {
            case 'day':
                startDate = endDate = new Date(currentDate.value);
                break;
            case 'week':
                startDate = getWeekStart(currentDate.value);
                endDate = getWeekEnd(currentDate.value);
                break;
            case 'month':
                const monthDays = getMonthDays();
                startDate = monthDays[0];
                endDate = monthDays[monthDays.length - 1];
                break;
        }
        
        // Limpiar registros previos
        registros.value = {};
        
        // Generar todas las fechas del rango
        const fechas: string[] = [];
        const currentDateLoop = new Date(startDate);
        
        while (currentDateLoop <= endDate) {
            fechas.push(formatDate(currentDateLoop));
            currentDateLoop.setDate(currentDateLoop.getDate() + 1);
        }
        
        // Cargar registros para cada fecha (manteniendo la funcionalidad original pero optimizada)
        const registrosPromises = fechas.map(async (fecha) => {
            return fetchRegistrosForDate(fecha);
        });
        
        // Ejecutar todas las promesas en paralelo
        await Promise.all(registrosPromises);
    } catch (error) {
        console.error('Error al cargar registros:', error);
    }
};

const fetchRegistrosForDate = async (dateStr: string) => {
    try {
        const promises = habitos.value.map(async (habito) => {
            const response = await axios.get(`/api/web/habitos/${habito.id}/registro/${dateStr}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                },
                withCredentials: true
            });
            return response.data.success && response.data.data ? response.data.data : null;
        });
        
        const dayRegistros = (await Promise.all(promises)).filter(Boolean);
        if (dayRegistros.length > 0) {
            registros.value[dateStr] = dayRegistros;
        }
    } catch (error) {
        // Es normal que no haya registros para algunos días
    }
};

// Cambiar modo de vista (con indicador de historial)
const changeViewMode = async (mode: 'day' | 'week' | 'month') => {
    const previousMode = viewMode.value;
    viewMode.value = mode;
    
    // Recargar registros con indicador para el nuevo modo
    if (previousMode !== mode) {
        loadingHistorial.value = true;
        try {
            await fetchRegistrosOptimized();
        } finally {
            loadingHistorial.value = false;
        }
    }
};

// Marcar/desmarcar hábito como completado
const toggleHabitoCompletion = async (habitoId: number, date: string) => {
    const isCompleted = isHabitoCompleted(habitoId, date);
    
    try {
        const endpoint = isCompleted 
            ? `/api/web/habitos/${habitoId}/descompletar`
            : `/api/web/habitos/${habitoId}/completar`;

        const response = await axios.post(endpoint, { fecha: date }, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            // Actualizar registros localmente
            if (!registros.value[date]) {
                registros.value[date] = [];
            }
            
            const existingIndex = registros.value[date].findIndex(r => r.habito_id === habitoId);
            if (existingIndex >= 0) {
                registros.value[date][existingIndex] = response.data.data.registro || response.data.data;
            } else {
                registros.value[date].push(response.data.data.registro || response.data.data);
            }
        }
    } catch (error) {
        console.error('Error al actualizar hábito:', error);
        alert('Error al actualizar el hábito');
    }
};

// Cargar datos al montar
onMounted(() => {
    loadData();
});
</script>

<template>
    <Head title="Calendario de Hábitos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gradient-kudos">
                        Calendario de Hábitos
                    </h1>
                    <p class="text-muted-foreground mt-1">
                        Visualiza y gestiona tus hábitos a lo largo del tiempo
                    </p>
                </div>
            </div>

            <!-- Controls -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <!-- Navigation -->
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" @click="navigatePrevious">
                                    <ChevronLeft class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="sm" @click="navigateNext">
                                    <ChevronRight class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="sm" @click="goToToday">
                                    Hoy
                                </Button>
                            </div>
                            <h2 class="text-lg font-semibold">{{ currentTitle }}</h2>
                        </div>

                        <!-- View Mode Selector -->
                        <div class="flex items-center gap-2">
                            <Button 
                                :variant="viewMode === 'day' ? 'default' : 'outline'" 
                                size="sm"
                                @click="changeViewMode('day')"
                            >
                                Día
                            </Button>
                            <Button 
                                :variant="viewMode === 'week' ? 'default' : 'outline'" 
                                size="sm"
                                @click="changeViewMode('week')"
                            >
                                Semana
                            </Button>
                            <Button 
                                :variant="viewMode === 'month' ? 'default' : 'outline'" 
                                size="sm"
                                @click="changeViewMode('month')"
                            >
                                Mes
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
                    <p class="mt-2 text-muted-foreground">Cargando calendario...</p>
                </div>
            </div>

            <!-- Calendar Views -->
            <div v-else>
                <!-- Day View -->
                <div v-if="viewMode === 'day'" class="space-y-4">
                    <!-- Indicador de carga de historial -->
                    <div v-if="loadingHistorial" class="flex items-center justify-center py-2 bg-muted/30 rounded-lg border border-dashed">
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <div class="w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                            <span>Cargando historial de hábitos...</span>
                        </div>
                    </div>
                    
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Calendar class="w-5 h-5 text-primary" />
                                {{ formatDisplayDate(currentDate) }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div 
                                    v-for="habito in habitos.filter(h => shouldShowHabit(h, currentDate))" 
                                    :key="habito.id"
                                    :class="[
                                        'p-3 border rounded-lg transition-colors',
                                        isToday(currentDate) 
                                            ? 'hover:bg-accent/50 cursor-pointer' 
                                            : 'opacity-60 cursor-not-allowed'
                                    ]"
                                >
                                    <!-- Horarios de recordatorios (arriba, en negrilla) -->
                                    <div 
                                        v-if="getHorasRecordatorios(habito.id, currentDate).length > 0"
                                        class="flex items-center gap-2 mb-3 text-primary/80"
                                    >
                                        <Clock class="w-4 h-4" />
                                        <span class="font-bold text-sm">
                                            {{ getHorasRecordatorios(habito.id, currentDate).join(' • ') }}
                                        </span>
                                    </div>
                                    
                                    <!-- Contenido del hábito -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <button
                                                @click="isToday(currentDate) ? toggleHabitoCompletion(habito.id, formatDate(currentDate)) : null"
                                                :class="[
                                                    'w-5 h-5 rounded border-2 flex items-center justify-center transition-all',
                                                    isHabitoCompleted(habito.id, formatDate(currentDate))
                                                        ? 'bg-primary border-primary' 
                                                        : isToday(currentDate)
                                                            ? 'border-muted-foreground/50 hover:border-primary cursor-pointer'
                                                            : 'border-muted-foreground/30 cursor-not-allowed'
                                                ]"
                                                :disabled="!isToday(currentDate)"
                                            >
                                                <Check 
                                                    v-if="isHabitoCompleted(habito.id, formatDate(currentDate))" 
                                                    class="w-4 h-4 text-primary-foreground" 
                                                />
                                            </button>
                                            <div>
                                                <p class="font-medium">{{ habito.nombre }}</p>
                                                <p class="text-sm text-muted-foreground">{{ habito.descripcion }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Badge variant="outline" class="text-xs">
                                                {{ habito.frecuencia }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Week View -->
                <div v-else-if="viewMode === 'week'" class="space-y-4">
                    <!-- Indicador de carga de historial -->
                    <div v-if="loadingHistorial" class="flex items-center justify-center py-2 bg-muted/30 rounded-lg border border-dashed">
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <div class="w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                            <span>Cargando historial de hábitos...</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-7 gap-2">
                        <div v-for="day in getWeekDays()" :key="day.toISOString()" class="min-h-[200px]">
                            <Card 
                                :class="[
                                    'h-full',
                                    day.toDateString() === new Date().toDateString() ? 'ring-2 ring-primary' : ''
                                ]"
                            >
                                <CardHeader class="p-3 pb-2">
                                    <CardTitle 
                                        :class="[
                                            'text-sm flex items-center gap-2',
                                            day.toDateString() === new Date().toDateString() ? 'text-primary font-bold' : ''
                                        ]"
                                    >
                                        {{ day.toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric' }) }}
                                        <span 
                                            v-if="day.toDateString() === new Date().toDateString()"
                                            class="w-2 h-2 bg-primary rounded-full animate-pulse"
                                        ></span>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent class="p-3 pt-0">
                                    <div class="space-y-2">
                                        <div 
                                            v-for="habito in habitos.filter(h => shouldShowHabit(h, day))" 
                                            :key="habito.id"
                                            :class="[
                                                'p-2 border rounded text-xs transition-colors',
                                                isToday(day) 
                                                    ? 'hover:bg-accent/50 cursor-pointer' 
                                                    : 'opacity-60 cursor-not-allowed'
                                            ]"
                                            @click="isToday(day) ? toggleHabitoCompletion(habito.id, formatDate(day)) : null"
                                        >
                                            <!-- Horarios de recordatorios (arriba, en negrilla) -->
                                            <div 
                                                v-if="getHorasRecordatorios(habito.id, day).length > 0"
                                                class="flex items-center gap-1 mb-2 text-primary/80"
                                            >
                                                <Clock class="w-3 h-3" />
                                                <span class="font-bold text-xs">
                                                    {{ getHorasRecordatorios(habito.id, day).join(' • ') }}
                                                </span>
                                            </div>
                                            
                                            <!-- Nombre del hábito con checkbox -->
                                            <div class="flex items-center gap-1">
                                                <div 
                                                    :class="[
                                                        'w-3 h-3 rounded border flex-shrink-0',
                                                        isHabitoCompleted(habito.id, formatDate(day))
                                                            ? 'bg-primary border-primary' 
                                                            : isToday(day)
                                                                ? 'border-muted-foreground/50'
                                                                : 'border-muted-foreground/30'
                                                    ]"
                                                >
                                                    <Check 
                                                        v-if="isHabitoCompleted(habito.id, formatDate(day))" 
                                                        class="w-3 h-3 text-primary-foreground" 
                                                    />
                                                </div>
                                                <span class="truncate">{{ habito.nombre }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>

                <!-- Month View -->
                <div v-else-if="viewMode === 'month'" class="space-y-4">
                    <!-- Days of week header -->
                    <div class="grid grid-cols-7 gap-2 mb-2">
                        <div v-for="day in ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']" :key="day" class="p-2 text-center text-sm font-medium text-muted-foreground">
                            {{ day }}
                        </div>
                    </div>
                    
                    <!-- Indicador de carga de historial -->
                    <div v-if="loadingHistorial" class="flex items-center justify-center py-3 bg-muted/30 rounded-lg border border-dashed">
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <div class="w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                            <span>Cargando historial de hábitos...</span>
                        </div>
                    </div>
                    
                    <!-- Calendar grid -->
                    <div class="grid grid-cols-7 gap-2">
                        <div v-for="day in getMonthDays()" :key="day.toISOString()" class="min-h-[120px]">
                            <Card 
                                :class="[
                                    'h-full',
                                    day.getMonth() !== currentDate.getMonth() ? 'opacity-50' : '',
                                    day.toDateString() === new Date().toDateString() ? 'ring-2 ring-primary' : ''
                                ]"
                            >
                                <CardHeader class="p-2 pb-1">
                                    <CardTitle class="text-sm">
                                        {{ day.getDate() }}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent class="p-2 pt-0">
                                    <div class="space-y-1">
                                        <div 
                                            v-for="habito in getHabitosForDay(day).slice(0, 3)" 
                                            :key="habito.id"
                                            :class="[
                                                'p-1 border rounded text-xs transition-colors',
                                                isToday(day) 
                                                    ? 'hover:bg-accent/50 cursor-pointer' 
                                                    : 'opacity-60 cursor-not-allowed'
                                            ]"
                                            @click="isToday(day) ? toggleHabitoCompletion(habito.id, formatDate(day)) : null"
                                        >
                                            <div class="flex items-center gap-1">
                                                <div 
                                                    :class="[
                                                        'w-2 h-2 rounded border flex-shrink-0',
                                                        isHabitoCompleted(habito.id, formatDate(day))
                                                            ? 'bg-primary border-primary' 
                                                            : isToday(day)
                                                                ? 'border-muted-foreground/50'
                                                                : 'border-muted-foreground/30'
                                                    ]"
                                                ></div>
                                                <span class="truncate">{{ habito.nombre }}</span>
                                            </div>
                                        </div>
                                        <div 
                                            v-if="getHabitosForDay(day).length > 3"
                                            class="text-xs text-muted-foreground text-center"
                                        >
                                            +{{ getHabitosForDay(day).length - 3 }} más
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!loading && habitos.length === 0" class="text-center py-12">
                <Calendar class="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                <h3 class="text-lg font-semibold mb-2">No tienes hábitos para mostrar</h3>
                <p class="text-muted-foreground mb-4">
                    Crea algunos hábitos para ver tu calendario personalizado
                </p>
                <Button @click="$inertia.visit('/habitos')" class="bg-gradient-kudos hover:opacity-90 text-white border-none hover-lift">
                    <Target class="w-4 h-4 mr-2" />
                    Crear Hábitos
                </Button>
            </div>
        </div>
    </AppLayout>
</template>