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

import { Plus, Target, Calendar, CheckCircle, Clock, Trash2, Edit, Eye } from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Objetivos',
        href: '/objetivos',
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
    habitos_count?: number;
    habitos_completados?: number;
    porcentaje_completado?: number;
}

// Estado reactivo
const objetivosList = ref<Objetivo[]>([]);
const loading = ref(true);
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const objetivoEditando = ref<Objetivo | null>(null);

// Función para formatear fecha local (evita conversión UTC)
const formatDateLocal = (date: Date = new Date()): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Formulario
const form = ref({
    nombre: '',
    descripcion: '',
    emoji: '🎯',
    color: '#3b82f6',
    tipo: 'salud',
    fecha_inicio: formatDateLocal(new Date()),
    fecha_objetivo: '',
});

const tiposObjetivo = [
    { value: 'salud', label: 'Salud' },
    { value: 'fitness', label: 'Fitness' },
    { value: 'educacion', label: 'Educación' },
    { value: 'finanzas', label: 'Finanzas' },
    { value: 'bienestar', label: 'Bienestar' },
    { value: 'carrera', label: 'Carrera' },
    { value: 'relaciones', label: 'Relaciones' },
    { value: 'otro', label: 'Otro' },
];

const emojisDisponibles = [
    '🎯', '💪', '🚀', '⭐', '🔥', '💎', '🌟', '🏆', 
    '📚', '💰', '❤️', '🌱', '🎨', '🧠', '⚡', '🌈'
];

const coloresDisponibles = [
    '#3b82f6', '#ef4444', '#22c55e', '#f59e0b', 
    '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'
];

// Obtener token CSRF
const getCSRFToken = () => {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    const token = metaTag ? metaTag.getAttribute('content') : '';
    console.log('CSRF Token:', token ? 'Found' : 'Not found');
    return token;
};

// Cargar objetivos
const fetchObjetivos = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/web/objetivos', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            objetivosList.value = response.data.data;
        }
    } catch (error: any) {
        console.error('Error al cargar objetivos:', error);
    } finally {
        loading.value = false;
    }
};

// Crear objetivo
const crearObjetivo = async () => {
    try {
        // Validación básica
        if (!form.value.nombre.trim()) {
            alert('El nombre del objetivo es requerido');
            return;
        }

        // Preparar datos con validaciones adicionales
        const dataToSend = {
            ...form.value,
            // Asegurar que las fechas estén en formato correcto
            fecha_inicio: form.value.fecha_inicio || formatDateLocal(new Date()),
            fecha_objetivo: form.value.fecha_objetivo || null,
            // Asegurar que activo esté definido
            activo: true
        };

        console.log('Datos a enviar:', dataToSend);

        const response = await axios.post('/api/web/objetivos', dataToSend, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        console.log('Respuesta del servidor:', response.data);

        if (response.data.success) {
            alert('¡Objetivo creado exitosamente!');
            await fetchObjetivos();
            resetForm();
            showCreateDialog.value = false;
        } else {
            alert('Error: ' + (response.data.message || 'Error desconocido'));
        }
    } catch (error: any) {
        console.error('Error al crear objetivo:', error);
        
        let errorMessage = 'Error al crear objetivo. ';
        
        if (error.response) {
            // El servidor respondió con un error
            console.log('Status:', error.response.status);
            console.log('Data:', error.response.data);
            
            if (error.response.status === 422) {
                // Errores de validación
                const errors = error.response.data.errors || {};
                errorMessage += 'Errores de validación:\n';
                for (const field in errors) {
                    errorMessage += `- ${field}: ${errors[field].join(', ')}\n`;
                }
            } else if (error.response.status === 401) {
                errorMessage += 'No estás autenticado. Por favor, inicia sesión.';
                window.location.href = '/login';
                return;
            } else {
                errorMessage += error.response.data.message || `Error del servidor (${error.response.status})`;
            }
        } else if (error.request) {
            errorMessage += 'No se pudo conectar con el servidor.';
        } else {
            errorMessage += error.message;
        }
        
        alert(errorMessage);
    }
};

// Editar objetivo
const editarObjetivo = async () => {
    if (!objetivoEditando.value) return;

    try {
        const response = await axios.patch(`/api/web/objetivos/${objetivoEditando.value.id}`, form.value, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            await fetchObjetivos();
            resetForm();
            showEditDialog.value = false;
            objetivoEditando.value = null;
        }
    } catch (error: any) {
        console.error('Error al editar objetivo:', error);
        alert('Error al editar objetivo. Por favor, intenta de nuevo.');
    }
};

// Eliminar objetivo
const eliminarObjetivo = async (objetivo: Objetivo) => {
    if (!confirm('¿Estás seguro de que quieres eliminar este objetivo?')) return;

    try {
        const response = await axios.delete(`/api/web/objetivos/${objetivo.id}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            await fetchObjetivos();
        }
    } catch (error: any) {
        console.error('Error al eliminar objetivo:', error);
        alert('Error al eliminar objetivo. Por favor, intenta de nuevo.');
    }
};

// Marcar objetivo como completado
const toggleCompletado = async (objetivo: Objetivo) => {
    try {
        const response = await axios.post(`/api/web/objetivos/${objetivo.id}/completar`, {}, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            await fetchObjetivos();
        }
    } catch (error: any) {
        console.error('Error al actualizar objetivo:', error);
        alert('Error al actualizar objetivo. Por favor, intenta de nuevo.');
    }
};

// Abrir modal de edición
const abrirEdicion = (objetivo: Objetivo) => {
    objetivoEditando.value = objetivo;
    form.value = {
        nombre: objetivo.nombre,
        descripcion: objetivo.descripcion,
        emoji: objetivo.emoji || '🎯',
        color: objetivo.color || '#3b82f6',
        tipo: objetivo.tipo,
        fecha_inicio: objetivo.fecha_inicio,
        fecha_objetivo: objetivo.fecha_objetivo || '',
    };
    showEditDialog.value = true;
};

// Resetear formulario
const resetForm = () => {
    form.value = {
        nombre: '',
        descripcion: '',
        emoji: '🎯',
        color: '#3b82f6',
        tipo: 'salud',
        fecha_inicio: formatDateLocal(new Date()),
        fecha_objetivo: '',
    };
};

// Ver detalle del objetivo (navegar a página específica)
const verDetalle = (objetivo: Objetivo) => {
    window.location.href = `/objetivos/${objetivo.id}`;
};

// Computed properties
const objetivosActivos = computed(() => objetivosList.value.filter(o => o.activo && !o.completado));
const objetivosCompletados = computed(() => objetivosList.value.filter(o => o.completado));

// Formatear fecha
const formatearFecha = (fecha: string | null) => {
    if (!fecha) return 'Sin fecha límite';
    return new Date(fecha).toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

// Calcular días restantes
const diasRestantes = (fechaObjetivo: string | null) => {
    if (!fechaObjetivo) return null;
    const hoy = new Date();
    const objetivo = new Date(fechaObjetivo);
    const diferencia = objetivo.getTime() - hoy.getTime();
    const dias = Math.ceil(diferencia / (1000 * 60 * 60 * 24));
    return dias;
};

// Función de prueba para verificar conectividad
const testConnection = async () => {
    try {
        console.log('Probando conectividad con el backend...');
        const response = await axios.get('/api/web/objetivos', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });
        console.log('Conexión exitosa:', response.data);
    } catch (error: any) {
        console.error('Error de conectividad:', error);
        if (error.response) {
            console.log('Status:', error.response.status);
            console.log('Headers:', error.response.headers);
            console.log('Data:', error.response.data);
        }
    }
};



// Cargar datos al montar el componente
onMounted(() => {
    // testConnection();
    fetchObjetivos();
});
</script>

<template>
    <Head title="Objetivos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gradient-kudos">Mis objetivos</h1>
                    <p class="text-muted-foreground">
                        Gestiona tus objetivos y los hábitos que te ayudarán a alcanzarlos
                    </p>
                </div>
                <Dialog v-model:open="showCreateDialog">
                    <DialogTrigger as-child>
                        <div class="flex gap-2">
                            <Button class="gap-2">
                                <Plus class="h-4 w-4" />
                                Crear objetivo
                            </Button>
                        </div>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>Crear nuevo objetivo</DialogTitle>
                            <DialogDescription>
                                Define tu objetivo y establece una fecha límite para mantener el enfoque.
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="crearObjetivo" class="space-y-4">
                            <!-- Nombre -->
                            <div class="space-y-2">
                                <Label for="nombre">Nombre del objetivo</Label>
                                <Input 
                                    id="nombre" 
                                    v-model="form.nombre" 
                                    placeholder="Ej: Perder 10 kg en 6 meses"
                                    required
                                />
                            </div>

                            <!-- Descripción -->
                            <div class="space-y-2">
                                <Label for="descripcion">Descripción</Label>
                                <textarea 
                                    id="descripcion" 
                                    v-model="form.descripcion" 
                                    placeholder="Describe tu objetivo y por qué es importante..."
                                    rows="3"
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                />
                            </div>

                            <!-- Emoji y Color -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label>Emoji</Label>
                                    <div class="grid grid-cols-4 gap-2">
                                        <button
                                            v-for="emoji in emojisDisponibles"
                                            :key="emoji"
                                            type="button"
                                            @click="form.emoji = emoji"
                                            :class="[
                                                'p-2 text-lg border rounded-md hover:bg-accent',
                                                form.emoji === emoji ? 'bg-accent border-primary' : ''
                                            ]"
                                        >
                                            {{ emoji }}
                                        </button>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Color</Label>
                                    <div class="grid grid-cols-4 gap-2">
                                        <button
                                            v-for="color in coloresDisponibles"
                                            :key="color"
                                            type="button"
                                            @click="form.color = color"
                                            :class="[
                                                'w-8 h-8 rounded-full border-2',
                                                form.color === color ? 'border-foreground' : 'border-muted'
                                            ]"
                                            :style="{ backgroundColor: color }"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Tipo -->
                            <div class="space-y-2">
                                <Label for="tipo">Tipo de objetivo</Label>
                                <select
                                    id="tipo"
                                    v-model="form.tipo"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="" disabled>Selecciona un tipo</option>
                                    <option 
                                        v-for="tipo in tiposObjetivo" 
                                        :key="tipo.value" 
                                        :value="tipo.value"
                                    >
                                        {{ tipo.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Fechas -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="fecha_inicio">Fecha de inicio</Label>
                                    <Input 
                                        id="fecha_inicio" 
                                        v-model="form.fecha_inicio" 
                                        type="date"
                                        required
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="fecha_objetivo">Fecha objetivo</Label>
                                    <Input 
                                        id="fecha_objetivo" 
                                        v-model="form.fecha_objetivo" 
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
                                    Crear objetivo
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Stats rápidas -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Objetivos activos</CardTitle>
                        <Target class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ objetivosActivos.length }}</div>
                        <p class="text-xs text-muted-foreground">En progreso</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Completados</CardTitle>
                        <CheckCircle class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ objetivosCompletados.length }}</div>
                        <p class="text-xs text-muted-foreground">Logrados</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ objetivosList.length }}</div>
                        <p class="text-xs text-muted-foreground">Objetivos creados</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Lista de objetivos activos -->
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Objetivos Activos</h2>
                    <div v-if="loading" class="text-center py-8">
                        <p class="text-muted-foreground">Cargando objetivos...</p>
                    </div>
                    <div v-else-if="objetivosActivos.length === 0" class="text-center py-8">
                        <Target class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                        <p class="text-muted-foreground">No tienes objetivos activos</p>
                        <Button @click="showCreateDialog = true" variant="outline" class="mt-2">
                            Crear tu primer objetivo
                        </Button>
                    </div>
                    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Card 
                            v-for="objetivo in objetivosActivos" 
                            :key="objetivo.id"
                            class="hover-lift cursor-pointer"
                            @click="verDetalle(objetivo)"
                        >
                            <CardHeader>
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">{{ objetivo.emoji }}</span>
                                        <div>
                                            <CardTitle class="text-base">{{ objetivo.nombre }}</CardTitle>
                                            <Badge 
                                                variant="secondary" 
                                                :style="{ backgroundColor: objetivo.color + '20', color: objetivo.color }"
                                            >
                                                {{ tiposObjetivo.find(t => t.value === objetivo.tipo)?.label }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div class="flex gap-1" @click.stop>
                                        <Button size="sm" variant="ghost" @click="abrirEdicion(objetivo)">
                                            <Edit class="h-3 w-3" />
                                        </Button>
                                        <Button size="sm" variant="ghost" @click="eliminarObjetivo(objetivo)">
                                            <Trash2 class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <CardDescription class="mb-3">
                                    {{ objetivo.descripcion || 'Sin descripción' }}
                                </CardDescription>
                                
                                <!-- Progreso si hay hábitos -->
                                <div v-if="objetivo.habitos_count" class="space-y-2 mb-3">
                                    <div class="flex justify-between text-sm">
                                        <span>Progreso</span>
                                        <span>{{ objetivo.porcentaje_completado || 0 }}%</span>
                                    </div>
                                    <div class="w-full bg-muted rounded-full h-2">
                                        <div 
                                            class="h-2 rounded-full transition-all" 
                                            :style="{ 
                                                width: `${objetivo.porcentaje_completado || 0}%`,
                                                backgroundColor: objetivo.color 
                                            }"
                                        />
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        {{ objetivo.habitos_completados || 0 }} de {{ objetivo.habitos_count }} hábitos
                                    </p>
                                </div>

                                <!-- Fecha objetivo -->
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Calendar class="h-3 w-3" />
                                    <span>{{ formatearFecha(objetivo.fecha_objetivo || null) }}</span>
                                </div>

                                <!-- Días restantes -->
                                <div v-if="objetivo.fecha_objetivo" class="mt-2">
                                    <Badge 
                                        :variant="diasRestantes(objetivo.fecha_objetivo)! > 30 ? 'secondary' : diasRestantes(objetivo.fecha_objetivo)! > 0 ? 'default' : 'destructive'"
                                    >
                                        <Clock class="h-3 w-3 mr-1" />
                                        {{ diasRestantes(objetivo.fecha_objetivo)! > 0 
                                            ? `${diasRestantes(objetivo.fecha_objetivo)} días restantes`
                                            : 'Fecha vencida'
                                        }}
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Objetivos completados -->
                <div v-if="objetivosCompletados.length > 0">
                    <h2 class="text-xl font-semibold mb-4">Objetivos Completados</h2>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Card 
                            v-for="objetivo in objetivosCompletados" 
                            :key="objetivo.id"
                            class="opacity-75 hover-lift cursor-pointer"
                            @click="verDetalle(objetivo)"
                        >
                            <CardHeader>
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">{{ objetivo.emoji }}</span>
                                        <div>
                                            <CardTitle class="text-base flex items-center gap-2">
                                                {{ objetivo.nombre }}
                                                <CheckCircle class="h-4 w-4 text-green-600" />
                                            </CardTitle>
                                            <Badge variant="secondary" class="bg-green-100 text-green-800">
                                                Completado
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <CardDescription class="mb-3">
                                    {{ objetivo.descripcion || 'Sin descripción' }}
                                </CardDescription>
                                
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <CheckCircle class="h-3 w-3" />
                                    <span>Completado el {{ formatearFecha(objetivo.fecha_completado || null) }}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de edición -->
        <Dialog v-model:open="showEditDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Editar objetivo</DialogTitle>
                    <DialogDescription>
                        Modifica los detalles de tu objetivo.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="editarObjetivo" class="space-y-4">
                    <!-- Mismo formulario que para crear, pero con editarObjetivo() -->
                    <!-- Nombre -->
                    <div class="space-y-2">
                        <Label for="edit-nombre">Nombre del objetivo</Label>
                        <Input 
                            id="edit-nombre" 
                            v-model="form.nombre" 
                            placeholder="Ej: Perder 10 kg en 6 meses"
                            required
                        />
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-2">
                        <Label for="edit-descripcion">Descripción</Label>
                        <textarea 
                            id="edit-descripcion" 
                            v-model="form.descripcion" 
                            placeholder="Describe tu objetivo y por qué es importante..."
                            rows="3"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <!-- Emoji y Color -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Emoji</Label>
                            <div class="grid grid-cols-4 gap-2">
                                <button
                                    v-for="emoji in emojisDisponibles"
                                    :key="emoji"
                                    type="button"
                                    @click="form.emoji = emoji"
                                    :class="[
                                        'p-2 text-lg border rounded-md hover:bg-accent',
                                        form.emoji === emoji ? 'bg-accent border-primary' : ''
                                    ]"
                                >
                                    {{ emoji }}
                                </button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>Color</Label>
                            <div class="grid grid-cols-4 gap-2">
                                <button
                                    v-for="color in coloresDisponibles"
                                    :key="color"
                                    type="button"
                                    @click="form.color = color"
                                    :class="[
                                        'w-8 h-8 rounded-full border-2',
                                        form.color === color ? 'border-foreground' : 'border-muted'
                                    ]"
                                    :style="{ backgroundColor: color }"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Tipo -->
                    <div class="space-y-2">
                        <Label for="edit-tipo">Tipo de objetivo</Label>
                        <select
                            id="edit-tipo"
                            v-model="form.tipo"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="" disabled>Selecciona un tipo</option>
                            <option 
                                v-for="tipo in tiposObjetivo" 
                                :key="tipo.value" 
                                :value="tipo.value"
                            >
                                {{ tipo.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Fechas -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="edit-fecha_inicio">Fecha de inicio</Label>
                            <Input 
                                id="edit-fecha_inicio" 
                                v-model="form.fecha_inicio" 
                                type="date"
                                required
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="edit-fecha_objetivo">Fecha objetivo</Label>
                            <Input 
                                id="edit-fecha_objetivo" 
                                v-model="form.fecha_objetivo" 
                                type="date"
                            />
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-2 justify-end">
                        <Button type="button" variant="outline" @click="showEditDialog = false">
                            Cancelar
                        </Button>
                        <Button type="submit">
                            Guardar cambios
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
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