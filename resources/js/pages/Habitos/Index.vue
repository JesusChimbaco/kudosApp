<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Plus, Calendar, Clock, Target, CheckSquare, Trash2, Bell, Edit } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';

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
    categoria?: Categoria;
    objetivo?: Objetivo;
    nombre: string;
    descripcion: string;
    frecuencia: string;
    fecha_inicio: string;
    fecha_fin?: string;
    dias_semana?: string | number[];
    activo: boolean;
    racha_actual?: number;
    racha_maxima?: number;
}

interface Categoria {
    id: number;
    nombre: string;
}

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Gestión de hábitos',
        href: '/habitos',
    },
];

// Estado de los hábitos
const habitos = ref<Habito[]>([]);
const categorias = ref<Categoria[]>([]);
const objetivos = ref<Objetivo[]>([]);
const loading = ref(true);
const isDialogOpen = ref(false);
const isEditMode = ref(false);
const editingId = ref<number | null>(null);

// Estado del formulario
const form = ref({
    categoria_id: '',
    objetivo_id: '',
    nombre: '',
    descripcion: '',
    frecuencia: 'diario',
    fecha_inicio: '',
    fecha_fin: '',
    dias_semana: [] as number[],
    activo: true
});

// Eliminamos categorías ya que no están en la base de datos
const frecuencias = [
    { value: 'diario', label: 'Diario' },
    { value: 'semanal', label: 'Semanal' },
    { value: 'mensual', label: 'Mensual' },
    { value: 'personalizado', label: 'Personalizado' }
];

// Obtener token CSRF
const getCSRFToken = () => {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
};

// Funciones
const fetchHabitos = async () => {
    try {
        loading.value = true;
        
        // Cargar hábitos y objetivos en paralelo
        const [habitosResponse, objetivosResponse] = await Promise.all([
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
            })
        ]);

        if (habitosResponse.data.success) {
            habitos.value = habitosResponse.data.data;
        }

        if (objetivosResponse.data.success) {
            objetivos.value = objetivosResponse.data.data;
        }
    } catch (error: any) {
        console.error('Error al cargar hábitos:', error);
        if (error.response?.status === 401) {
            alert('No estás autenticado. Por favor, inicia sesión.');
            window.location.href = '/login';
        } else {
            alert('No se pudieron cargar los hábitos');
        }
    } finally {
        loading.value = false;
    }
};

const fetchCategorias = async () => {
    try {
        const response = await axios.get('/api/web/categorias', {
            headers: {
                'X-CSRF-TOKEN': getCSRFToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (response.data.success) {
            categorias.value = response.data.data;
        }
    } catch (error: any) {
        console.error('Error al cargar categorías:', error);
        // No mostramos alert para categorías ya que no es crítico
    }
};

const createHabito = async () => {
    if (!form.value.nombre.trim()) {
        alert('El nombre del hábito es requerido');
        return;
    }

    try {
        const formData = {
            ...form.value,
            // Convertir días_semana a string si es semanal
            dias_semana: form.value.frecuencia === 'semanal' 
                ? form.value.dias_semana.map(d => {
                    const diasSemana = ['D', 'L', 'M', 'X', 'J', 'V', 'S'];
                    return diasSemana[d];
                }).join('')
                : null
        };

        const response = await axios.post('/api/web/habitos', formData, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            alert('¡Hábito creado correctamente!');
            
            // Limpiar formulario y cerrar dialog
            resetForm();
            isDialogOpen.value = false;
            
            // Recargar hábitos
            await fetchHabitos();
        }
    } catch (error: any) {
        console.error('Error al crear hábito:', error);
        
        if (error.response?.status === 401) {
            alert('No estás autenticado. Por favor, inicia sesión.');
            window.location.href = '/login';
        } else if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            let errorMessage = 'Errores de validación:\n';
            for (const field in errors) {
                errorMessage += `- ${errors[field].join(', ')}\n`;
            }
            alert(errorMessage);
        } else {
            const errorMessage = error.response?.data?.message || 'Error al crear el hábito';
            alert(errorMessage);
        }
    }
};

const deleteHabito = async (id: number) => {
    if (!confirm('¿Estás seguro de que quieres eliminar este hábito?')) {
        return;
    }

    try {
        const response = await axios.delete(`/api/web/habitos/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            alert('¡Hábito eliminado correctamente!');
            await fetchHabitos();
        }
    } catch (error: any) {
        console.error('Error al eliminar hábito:', error);
        if (error.response?.status === 401) {
            alert('No estás autenticado. Por favor, inicia sesión.');
            window.location.href = '/login';
        } else {
            alert('Error al eliminar el hábito');
        }
    }
};

const toggleActivo = async (id: number) => {
    try {
        const response = await axios.patch(`/api/web/habitos/${id}/toggle-activo`, {}, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            alert('Estado del hábito actualizado');
            await fetchHabitos();
        }
    } catch (error: any) {
        console.error('Error al cambiar estado:', error);
        if (error.response?.status === 401) {
            alert('No estás autenticado. Por favor, inicia sesión.');
            window.location.href = '/login';
        } else {
            alert('Error al cambiar el estado del hábito');
        }
    }
};

const editHabito = async (id: number) => {
    try {
        const response = await axios.get(`/api/web/habitos/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            const habito = response.data.data;
            
            // Cargar datos en el formulario
            form.value = {
                categoria_id: habito.categoria_id?.toString() || '',
                objetivo_id: habito.objetivo_id?.toString() || '',
                nombre: habito.nombre,
                descripcion: habito.descripcion || '',
                frecuencia: habito.frecuencia,
                fecha_inicio: habito.fecha_inicio ? habito.fecha_inicio.split('T')[0] : '',
                fecha_fin: habito.fecha_fin ? habito.fecha_fin.split('T')[0] : '',
                dias_semana: parseDiasSemana(habito.dias_semana),
                activo: habito.activo
            };
            
            isEditMode.value = true;
            editingId.value = id;
            isDialogOpen.value = true;
        }
    } catch (error: any) {
        console.error('Error al cargar hábito:', error);
        alert('Error al cargar los datos del hábito');
    }
};

const updateHabito = async () => {
    if (!form.value.nombre.trim()) {
        alert('El nombre del hábito es requerido');
        return;
    }

    try {
        const formData = {
            ...form.value,
            // Convertir días_semana a string si es semanal
            dias_semana: form.value.frecuencia === 'semanal' 
                ? form.value.dias_semana.map(d => {
                    const diasSemana = ['D', 'L', 'M', 'X', 'J', 'V', 'S'];
                    return diasSemana[d];
                }).join('')
                : null
        };

        const response = await axios.put(`/api/web/habitos/${editingId.value}`, formData, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            alert('¡Hábito actualizado correctamente!');
            
            // Limpiar formulario y cerrar dialog
            resetForm();
            isEditMode.value = false;
            editingId.value = null;
            isDialogOpen.value = false;
            
            // Recargar hábitos
            await fetchHabitos();
        }
    } catch (error: any) {
        console.error('Error al actualizar hábito:', error);
        
        if (error.response?.status === 401) {
            alert('No estás autenticado. Por favor, inicia sesión.');
            window.location.href = '/login';
        } else if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            let errorMessage = 'Errores de validación:\n';
            for (const field in errors) {
                errorMessage += `- ${errors[field].join(', ')}\n`;
            }
            alert(errorMessage);
        } else {
            const errorMessage = error.response?.data?.message || 'Error al actualizar el hábito';
            alert(errorMessage);
        }
    }
};

const saveHabito = async () => {
    if (isEditMode.value) {
        await updateHabito();
    } else {
        await createHabito();
    }
};

const openCreateDialog = () => {
    resetForm();
    isEditMode.value = false;
    editingId.value = null;
    isDialogOpen.value = true;
};

const resetForm = () => {
    form.value = {
        categoria_id: '',
        objetivo_id: '',
        nombre: '',
        descripcion: '',
        frecuencia: 'diario',
        fecha_inicio: '',
        fecha_fin: '',
        dias_semana: [],
        activo: true
    };
    isEditMode.value = false;
    editingId.value = null;
};

// Función para parsear días de la semana desde string
const parseDiasSemana = (diasSemana: string | number[] | undefined): number[] => {
    if (!diasSemana) return [];
    if (Array.isArray(diasSemana)) return diasSemana;
    if (typeof diasSemana === 'string') {
        const diasMap: { [key: string]: number } = {
            'D': 0, 'L': 1, 'M': 2, 'X': 3, 'J': 4, 'V': 5, 'S': 6
        };
        return diasSemana.split('').map(d => diasMap[d]).filter(d => d !== undefined);
    }
    return [];
};

// Función para alternar día de la semana
const toggleDiaSemana = (dia: number) => {
    const index = form.value.dias_semana.indexOf(dia);
    if (index > -1) {
        form.value.dias_semana.splice(index, 1);
    } else {
        form.value.dias_semana.push(dia);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-ES');
};

const getFrecuenciaLabel = (frecuencia: string) => {
    const freq = frecuencias.find(f => f.value === frecuencia);
    return freq?.label || frecuencia;
};

// Cargar datos al montar el componente
onMounted(() => {
    // Establecer fecha de inicio por defecto (hoy)
    form.value.fecha_inicio = new Date().toISOString().split('T')[0];
    fetchHabitos();
    fetchCategorias();
});
</script>

<template>
    <Head title="Gestión de hábitos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gradient-kudos">Gestión de hábitos</h1>
                    <p class="text-muted-foreground">
                        Administra tus hábitos y haz seguimiento de tu progreso
                    </p>
                </div>
                
                <Dialog v-model:open="isDialogOpen">
                    <DialogTrigger as-child>
                        <Button 
                            class="gap-2 bg-gradient-kudos hover:opacity-90 text-white border-none hover-lift"
                            @click="openCreateDialog"
                        >
                            <Plus class="w-4 h-4" />
                            Crear hábito
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>{{ isEditMode ? 'Editar Hábito' : 'Crear Nuevo Hábito' }}</DialogTitle>
                            <DialogDescription>
                                {{ isEditMode ? 'Modifica la información del hábito' : 'Completa la información para crear un nuevo hábito' }}
                            </DialogDescription>
                        </DialogHeader>
                        
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label for="nombre">Nombre del Hábito</Label>
                                <Input
                                    id="nombre"
                                    v-model="form.nombre"
                                    placeholder="Ej: Hacer ejercicio"
                                    required
                                />
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="descripcion">Descripción</Label>
                                <textarea
                                    id="descripcion"
                                    v-model="form.descripcion"
                                    placeholder="Describe tu hábito..."
                                    rows="3"
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                />
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="categoria">Categoría</Label>
                                <select
                                    v-model="form.categoria_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="">Selecciona una categoría</option>
                                    <option
                                        v-for="categoria in categorias"
                                        :key="categoria.id"
                                        :value="categoria.id"
                                    >
                                        {{ categoria.nombre }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="objetivo">Objetivo (Opcional)</Label>
                                <select
                                    v-model="form.objetivo_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">Sin objetivo específico</option>
                                    <option
                                        v-for="objetivo in objetivos.filter(o => o.activo)"
                                        :key="objetivo.id"
                                        :value="objetivo.id"
                                    >
                                        {{ objetivo.emoji }} {{ objetivo.nombre }}
                                    </option>
                                </select>
                                <p class="text-xs text-muted-foreground">
                                    Asocia este hábito con un objetivo para mejor seguimiento
                                </p>
                            </div>
                            
                            <div class="grid grid-cols-1 gap-4">
                                <div class="space-y-2">
                                    <Label for="frecuencia">Frecuencia</Label>
                                    <select
                                        v-model="form.frecuencia"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option
                                            v-for="freq in frecuencias"
                                            :key="freq.value"
                                            :value="freq.value"
                                        >
                                            {{ freq.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Días de la semana (solo si es semanal) -->
                            <div v-if="form.frecuencia === 'semanal'" class="space-y-2">
                                <Label>Días de la semana</Label>
                                <div class="flex gap-2">
                                    <button
                                        v-for="(dia, index) in ['D', 'L', 'M', 'X', 'J', 'V', 'S']"
                                        :key="index"
                                        type="button"
                                        @click="toggleDiaSemana(index)"
                                        :class="[
                                            'w-8 h-8 rounded-full border text-sm font-medium',
                                            form.dias_semana.includes(index)
                                                ? 'bg-primary text-primary-foreground border-primary'
                                                : 'border-muted-foreground/20 hover:border-primary'
                                        ]"
                                    >
                                        {{ dia }}
                                    </button>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Selecciona los días en que quieres realizar este hábito
                                </p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="fecha_inicio">Fecha de Inicio</Label>
                                    <Input
                                        id="fecha_inicio"
                                        v-model="form.fecha_inicio"
                                        type="date"
                                        required
                                    />
                                </div>
                                
                                <div class="space-y-2">
                                    <Label for="fecha_fin">Fecha Objetivo (Opcional)</Label>
                                    <Input
                                        id="fecha_fin"
                                        v-model="form.fecha_fin"
                                        type="date"
                                    />
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                La fecha objetivo te ayuda a establecer metas específicas y medir tu progreso
                            </p>
                        </div>
                        
                        <DialogFooter>
                            <Button variant="outline" @click="isDialogOpen = false">
                                Cancelar
                            </Button>
                            <Button @click="saveHabito">
                                {{ isEditMode ? 'Actualizar' : 'Crear' }} Hábito
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
                    <p class="mt-2 text-muted-foreground">Cargando hábitos...</p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="habitos.length === 0" class="text-center py-12">
                <CheckSquare class="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                <h3 class="text-lg font-semibold mb-2">No tienes hábitos creados</h3>
                <p class="text-muted-foreground mb-4">
                    Comienza tu journey creando tu primer hábito
                </p>
                <Button @click="isDialogOpen = true">
                    <Plus class="w-4 h-4 mr-2" />
                    Crear mi primer hábito
                </Button>
            </div>

            <!-- Hábitos List -->
            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="habito in habitos"
                    :key="habito.id"
                    class="relative flex flex-col"
                >
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <CardTitle class="text-lg">{{ habito.nombre }}</CardTitle>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <Badge v-if="habito.categoria" variant="outline" class="text-xs">
                                        {{ habito.categoria.nombre }}
                                    </Badge>
                                    <Badge v-if="habito.objetivo" variant="secondary" class="text-xs">
                                        {{ habito.objetivo.emoji }} {{ habito.objetivo.nombre }}
                                    </Badge>
                                </div>
                                <CardDescription>{{ habito.descripcion }}</CardDescription>
                            </div>
                            <Badge
                                :variant="habito.activo ? 'default' : 'secondary'"
                                class="ml-2"
                            >
                                {{ habito.activo ? 'Activo' : 'Inactivo' }}
                            </Badge>
                        </div>
                    </CardHeader>
                    
                    <CardContent class="space-y-3 flex-1 flex flex-col">
                        <div>
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Clock class="w-4 h-4" />
                                <span>{{ getFrecuenciaLabel(habito.frecuencia) }}</span>
                            </div>

                            <!-- Mostrar días de la semana si es semanal -->
                            <div v-if="habito.frecuencia === 'semanal' && habito.dias_semana" class="flex items-center gap-2 text-sm text-muted-foreground mt-2">
                                <Target class="w-4 h-4" />
                                <span>Días: {{ typeof habito.dias_semana === 'string' ? habito.dias_semana : habito.dias_semana.join(', ') }}</span>
                            </div>

                            <!-- Mostrar rachas si existen -->
                            <div v-if="habito.racha_actual && habito.racha_actual > 0" class="flex items-center gap-2 text-sm text-orange-600 mt-2">
                                <span class="text-lg">🔥</span>
                                <span>{{ habito.racha_actual }} días</span>
                                <span v-if="habito.racha_maxima && habito.racha_maxima > habito.racha_actual" class="text-muted-foreground">
                                    (máx: {{ habito.racha_maxima }})
                                </span>
                            </div>

                            <div class="text-sm text-muted-foreground mt-2">
                                <div class="flex items-center gap-2">
                                    <Calendar class="w-4 h-4" />
                                    <span>Inicio: {{ formatDate(habito.fecha_inicio) }}</span>
                                </div>
                                <div v-if="habito.fecha_fin" class="flex items-center gap-2 mt-1">
                                    <Target class="w-4 h-4" />
                                    <span>Objetivo: {{ formatDate(habito.fecha_fin) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer actions: left column (recordatorios, desactivar) and right column (editar, eliminar) -->
                        <div class="mt-auto pt-4">
                            <div class="grid grid-cols-2 gap-4 items-end">
                                <!-- Left column: stacked actions -->
                                <div class="flex flex-col gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="$inertia.visit(`/habitos/${habito.id}/recordatorios`)"
                                        title="Gestionar recordatorios"
                                    >
                                        <Bell class="w-4 h-4 mr-1" />
                                        Recordatorios
                                    </Button>

                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="toggleActivo(habito.id)"
                                        title="Activar/Desactivar"
                                    >
                                        {{ habito.activo ? 'Desactivar' : 'Activar' }}
                                    </Button>
                                </div>

                                <!-- Right column: edit/delete aligned to right -->
                                <div class="flex items-center justify-end gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="editHabito(habito.id)"
                                        title="Editar hábito"
                                    >
                                        <Edit class="w-4 h-4" />
                                    </Button>

                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="deleteHabito(habito.id)"
                                        title="Eliminar hábito"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>