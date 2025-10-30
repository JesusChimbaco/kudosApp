<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Plus, ArrowLeft, Bell, Clock, Calendar, Trash2, Edit, ToggleLeft, ToggleRight } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';

// Props
const props = defineProps<{
    habitoId: number;
}>();

// Interfaces
interface Recordatorio {
    id: number;
    habito_id: number;
    activo: boolean;
    hora: string;
    dias_semana: string | null;
    tipo: 'email' | 'push';
    mensaje_personalizado: string | null;
    created_at: string;
    updated_at: string;
}

interface Habito {
    id: number;
    nombre: string;
    descripcion: string;
    emoji?: string;
    color?: string;
}

// Estado
const habito = ref<Habito | null>(null);
const recordatorios = ref<Recordatorio[]>([]);
const loading = ref(true);
const isDialogOpen = ref(false);
const isEditMode = ref(false);
const editingId = ref<number | null>(null);

// Formulario
const form = ref({
    hora: '',
    dias_semana: [] as string[],
    tipo: 'email' as 'email' | 'push',
    mensaje_personalizado: '',
    activo: true
});

// Días de la semana
const diasSemana = [
    { value: 'L', label: 'Lun', fullLabel: 'Lunes' },
    { value: 'M', label: 'Mar', fullLabel: 'Martes' },
    { value: 'X', label: 'Mié', fullLabel: 'Miércoles' },
    { value: 'J', label: 'Jue', fullLabel: 'Jueves' },
    { value: 'V', label: 'Vie', fullLabel: 'Viernes' },
    { value: 'S', label: 'Sáb', fullLabel: 'Sábado' },
    { value: 'D', label: 'Dom', fullLabel: 'Domingo' },
];

// Breadcrumbs
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Hábitos',
        href: '/habitos',
    },
    {
        title: habito.value?.nombre || 'Recordatorios',
        href: `/habitos/${props.habitoId}/recordatorios`,
    },
]);

// Obtener token CSRF
const getCSRFToken = () => {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
};

// Funciones
const fetchHabito = async () => {
    try {
        const response = await axios.get(`/api/web/habitos/${props.habitoId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            habito.value = response.data.data;
        }
    } catch (error: any) {
        console.error('Error al cargar hábito:', error);
        alert('Error al cargar la información del hábito');
    }
};

const fetchRecordatorios = async () => {
    try {
        loading.value = true;
        const response = await axios.get(`/api/web/habitos/${props.habitoId}/recordatorios`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            withCredentials: true
        });

        if (response.data.success) {
            recordatorios.value = response.data.data;
        }
    } catch (error: any) {
        console.error('Error al cargar recordatorios:', error);
        alert('Error al cargar los recordatorios');
    } finally {
        loading.value = false;
    }
};

const saveRecordatorio = async () => {
    try {
        const data = {
            hora: form.value.hora,
            dias_semana: form.value.dias_semana.length > 0 ? form.value.dias_semana.join(',') : null,
            tipo: form.value.tipo,
            mensaje_personalizado: form.value.mensaje_personalizado || null,
            activo: form.value.activo
        };

        let response;
        if (isEditMode.value && editingId.value) {
            // Actualizar
            response = await axios.patch(
                `/api/web/habitos/${props.habitoId}/recordatorios/${editingId.value}`,
                data,
                {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken(),
                    },
                    withCredentials: true
                }
            );
        } else {
            // Crear
            response = await axios.post(
                `/api/web/habitos/${props.habitoId}/recordatorios`,
                data,
                {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken(),
                    },
                    withCredentials: true
                }
            );
        }

        if (response.data.success) {
            alert(isEditMode.value ? 'Recordatorio actualizado' : 'Recordatorio creado exitosamente');
            resetForm();
            isDialogOpen.value = false;
            await fetchRecordatorios();
        }
    } catch (error: any) {
        console.error('Error al guardar recordatorio:', error);
        if (error.response?.status === 422) {
            const errors = error.response.data.errors || error.response.data;
            let errorMessage = 'Errores de validación:\n';
            for (const field in errors) {
                errorMessage += `- ${errors[field]}\n`;
            }
            alert(errorMessage);
        } else {
            alert(error.response?.data?.message || 'Error al guardar el recordatorio');
        }
    }
};

const editRecordatorio = (recordatorio: Recordatorio) => {
    isEditMode.value = true;
    editingId.value = recordatorio.id;
    
    form.value = {
        hora: recordatorio.hora.substring(0, 5), // HH:mm
        dias_semana: recordatorio.dias_semana ? recordatorio.dias_semana.split(',') : [],
        tipo: recordatorio.tipo,
        mensaje_personalizado: recordatorio.mensaje_personalizado || '',
        activo: recordatorio.activo
    };
    
    isDialogOpen.value = true;
};

const deleteRecordatorio = async (id: number) => {
    if (!confirm('¿Estás seguro de que quieres eliminar este recordatorio?')) {
        return;
    }

    try {
        const response = await axios.delete(
            `/api/web/habitos/${props.habitoId}/recordatorios/${id}`,
            {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                },
                withCredentials: true
            }
        );

        if (response.data.success) {
            alert('Recordatorio eliminado');
            await fetchRecordatorios();
        }
    } catch (error: any) {
        console.error('Error al eliminar:', error);
        alert('Error al eliminar el recordatorio');
    }
};

const toggleRecordatorio = async (id: number) => {
    try {
        const response = await axios.post(
            `/api/web/habitos/${props.habitoId}/recordatorios/${id}/toggle`,
            {},
            {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                },
                withCredentials: true
            }
        );

        if (response.data.success) {
            await fetchRecordatorios();
        }
    } catch (error: any) {
        console.error('Error al cambiar estado:', error);
        alert('Error al cambiar el estado del recordatorio');
    }
};

const toggleDia = (dia: string) => {
    const index = form.value.dias_semana.indexOf(dia);
    if (index > -1) {
        form.value.dias_semana.splice(index, 1);
    } else {
        form.value.dias_semana.push(dia);
    }
};

const resetForm = () => {
    form.value = {
        hora: '',
        dias_semana: [],
        tipo: 'email',
        mensaje_personalizado: '',
        activo: true
    };
    isEditMode.value = false;
    editingId.value = null;
};

const formatHora = (hora: string) => {
    return hora.substring(0, 5); // HH:mm
};

const formatDiasSemana = (dias: string | null) => {
    if (!dias) return 'Todos los días';
    return dias;
};

const goBack = () => {
    router.visit('/habitos');
};

// Cargar datos
onMounted(() => {
    fetchHabito();
    fetchRecordatorios();
});
</script>

<template>
    <Head :title="`Recordatorios - ${habito?.nombre || 'Hábito'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" @click="goBack">
                        <ArrowLeft class="w-5 h-5" />
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <span v-if="habito?.emoji" class="text-4xl">{{ habito.emoji }}</span>
                            <h1 class="text-3xl font-bold tracking-tight">{{ habito?.nombre }}</h1>
                        </div>
                        <p class="text-muted-foreground mt-1">
                            Gestiona los recordatorios para este hábito
                        </p>
                    </div>
                </div>
                
                <Dialog v-model:open="isDialogOpen" @update:open="!$event && resetForm()">
                    <DialogTrigger as-child>
                        <Button class="gap-2 bg-gradient-kudos hover:opacity-90 text-white border-none">
                            <Plus class="w-4 h-4" />
                            Nuevo Recordatorio
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-lg">
                        <DialogHeader>
                            <DialogTitle>
                                {{ isEditMode ? 'Editar Recordatorio' : 'Nuevo Recordatorio' }}
                            </DialogTitle>
                            <DialogDescription>
                                Configura cuándo quieres recibir notificaciones sobre este hábito
                            </DialogDescription>
                        </DialogHeader>
                        
                        <div class="space-y-4">
                            <!-- Hora -->
                            <div class="space-y-2">
                                <Label for="hora" class="flex items-center gap-2">
                                    <Clock class="w-4 h-4" />
                                    Hora del recordatorio
                                </Label>
                                <Input
                                    id="hora"
                                    v-model="form.hora"
                                    type="time"
                                    required
                                />
                            </div>

                            <!-- Días de la semana -->
                            <div class="space-y-2">
                                <Label class="flex items-center gap-2">
                                    <Calendar class="w-4 h-4" />
                                    Días de la semana
                                </Label>
                                <p class="text-xs text-muted-foreground">
                                    Deja vacío para recibir el recordatorio todos los días
                                </p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <button
                                        v-for="dia in diasSemana"
                                        :key="dia.value"
                                        type="button"
                                        @click="toggleDia(dia.value)"
                                        :class="[
                                            'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                                            form.dias_semana.includes(dia.value)
                                                ? 'bg-primary text-primary-foreground'
                                                : 'bg-secondary text-secondary-foreground hover:bg-secondary/80'
                                        ]"
                                    >
                                        {{ dia.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Tipo -->
                            <div class="space-y-2">
                                <Label for="tipo" class="flex items-center gap-2">
                                    <Bell class="w-4 h-4" />
                                    Tipo de notificación
                                </Label>
                                <select
                                    id="tipo"
                                    v-model="form.tipo"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                >
                                    <option value="email">📧 Email</option>
                                    <option value="push" disabled>📱 Push (Próximamente)</option>
                                </select>
                            </div>

                            <!-- Mensaje personalizado -->
                            <div class="space-y-2">
                                <Label for="mensaje">Mensaje personalizado (opcional)</Label>
                                <textarea
                                    id="mensaje"
                                    v-model="form.mensaje_personalizado"
                                    placeholder="Ej: ¡Es hora de hacer ejercicio! 💪"
                                    rows="3"
                                    maxlength="500"
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                />
                                <p class="text-xs text-muted-foreground">
                                    {{ form.mensaje_personalizado.length }}/500 caracteres
                                </p>
                            </div>

                            <!-- Activo -->
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div class="flex-1">
                                    <Label for="activo" class="cursor-pointer font-medium">
                                        Recordatorio activo
                                    </Label>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        {{ form.activo ? 'El recordatorio se enviará' : 'El recordatorio está pausado' }}
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        id="activo"
                                        type="checkbox"
                                        v-model="form.activo"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                        
                        <DialogFooter>
                            <Button variant="outline" @click="isDialogOpen = false">
                                Cancelar
                            </Button>
                            <Button @click="saveRecordatorio">
                                {{ isEditMode ? 'Actualizar' : 'Crear' }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
                    <p class="mt-2 text-muted-foreground">Cargando recordatorios...</p>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else-if="recordatorios.length === 0" class="text-center py-12">
                <Bell class="w-16 h-16 mx-auto text-muted-foreground/50 mb-4" />
                <h3 class="text-lg font-semibold mb-2">Sin recordatorios</h3>
                <p class="text-muted-foreground mb-6">
                    No has configurado ningún recordatorio para este hábito
                </p>
                <Button @click="isDialogOpen = true" class="gap-2">
                    <Plus class="w-4 h-4" />
                    Crear Primer Recordatorio
                </Button>
            </div>

            <!-- Lista de recordatorios -->
            <div v-else class="grid gap-4">
                <Card
                    v-for="recordatorio in recordatorios"
                    :key="recordatorio.id"
                    :class="[
                        'transition-all',
                        !recordatorio.activo && 'opacity-60'
                    ]"
                >
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex items-center gap-2 text-2xl font-bold">
                                        <Clock class="w-6 h-6 text-primary" />
                                        {{ formatHora(recordatorio.hora) }}
                                    </div>
                                    <Badge :variant="recordatorio.activo ? 'default' : 'secondary'">
                                        {{ recordatorio.activo ? 'Activo' : 'Inactivo' }}
                                    </Badge>
                                    <Badge variant="outline">
                                        {{ recordatorio.tipo === 'email' ? '📧 Email' : '📱 Push' }}
                                    </Badge>
                                </div>

                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center gap-2 text-muted-foreground">
                                        <Calendar class="w-4 h-4" />
                                        <span>{{ formatDiasSemana(recordatorio.dias_semana) }}</span>
                                    </div>

                                    <div v-if="recordatorio.mensaje_personalizado" class="mt-3 p-3 bg-muted rounded-md">
                                        <p class="text-sm italic">"{{ recordatorio.mensaje_personalizado }}"</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="toggleRecordatorio(recordatorio.id)"
                                    :title="recordatorio.activo ? 'Desactivar' : 'Activar'"
                                >
                                    <component
                                        :is="recordatorio.activo ? ToggleRight : ToggleLeft"
                                        class="w-5 h-5"
                                        :class="recordatorio.activo ? 'text-primary' : 'text-muted-foreground'"
                                    />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="editRecordatorio(recordatorio)"
                                >
                                    <Edit class="w-4 h-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="deleteRecordatorio(recordatorio.id)"
                                    class="text-destructive hover:text-destructive"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Info card -->
            <Card class="bg-muted/50 border-dashed">
                <CardContent class="p-6">
                    <h3 class="font-semibold mb-2">💡 Información sobre recordatorios</h3>
                    <ul class="text-sm text-muted-foreground space-y-1">
                        <li>• Los recordatorios se envían automáticamente a tu email</li>
                        <li>• Puedes configurar múltiples recordatorios para el mismo hábito</li>
                        <li>• Si no seleccionas días, recibirás el recordatorio todos los días</li>
                        <li>• Puedes desactivar temporalmente un recordatorio sin eliminarlo</li>
                    </ul>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.bg-gradient-kudos {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
