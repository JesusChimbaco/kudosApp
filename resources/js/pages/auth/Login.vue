<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Iniciar Sesión - KUDOS">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
    </Head>

    <div class="min-h-screen bg-gradient-to-br from-white via-purple-50 to-emerald-50 flex items-center justify-center p-3">
        <!-- Single centered container -->
        <div class="w-full max-w-3xl flex bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
            <!-- Left Side - Branding & Info -->
            <div class="w-1/2 bg-gradient-to-br from-purple-600 to-emerald-500 p-6 flex flex-col justify-center text-white" style="background: linear-gradient(135deg, #6E49C7 0%, #10B981 100%);">
                <!-- Logo -->
                <div class="mb-5">
                    <Link href="/" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-emerald-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">K</span>
                        </div>
                        <span class="text-xl font-bold font-instrument">KUDOS</span>
                    </Link>
                </div>

                <!-- Hero Content -->
                <div>
                    <h1 class="text-2xl font-bold mb-3 font-instrument">
                        Bienvenido de vuelta
                    </h1>
                    <p class="text-purple-100 mb-5 text-xs leading-relaxed">
                        Continúa construyendo mejores hábitos y alcanzando tus metas con KUDOS.
                    </p>

                    <!-- Benefits -->
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path>
                                </svg>
                            </div>
                            <span class="text-purple-100 text-xs">Retoma tu progreso</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path>
                                </svg>
                            </div>
                            <span class="text-purple-100 text-xs">Accede a tus hábitos</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path>
                                </svg>
                            </div>
                            <span class="text-purple-100 text-xs">Continúa tu transformación</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="w-1/2 p-6 flex flex-col justify-center">
                <div class="w-full max-w-xs mx-auto">
                    <!-- Form Header -->
                    <div class="text-center mb-4">
                        <h2 class="text-xl font-bold text-gray-900 mb-1 font-instrument">Iniciar sesión</h2>
                        <p class="text-xs text-gray-600">Accede a tu cuenta de KUDOS</p>
                    </div>

                    <!-- Status Message -->
                    <div
                        v-if="status"
                        class="mb-3 text-center text-xs font-medium text-green-600 bg-green-50 p-2 rounded-md"
                    >
                        {{ status }}
                    </div>

                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="space-y-3"
                    >
                        <!-- Email -->
                        <div>
                            <Label for="email" class="text-xs font-medium text-gray-700 mb-1 block">Correo electrónico</Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="tu-email@ejemplo.com"
                                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            />
                            <InputError :message="errors.email" class="text-red-500 text-xs mt-1" />
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <Label for="password" class="text-xs font-medium text-gray-700">Contraseña</Label>
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="request()"
                                    class="text-xs text-purple-600 hover:text-purple-700 transition-colors duration-200"
                                    :tabindex="5"
                                >
                                    ¿Olvidaste tu contraseña?
                                </TextLink>
                            </div>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="Tu contraseña"
                                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            />
                            <InputError :message="errors.password" class="text-red-500 text-xs mt-1" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center">
                            <Label for="remember" class="flex items-center space-x-2">
                                <Checkbox id="remember" name="remember" :tabindex="3" class="text-purple-600" />
                                <span class="text-xs text-gray-700">Recordarme</span>
                            </Label>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                            class="w-full py-2 px-4 bg-purple-600 text-white font-medium text-xs rounded-md hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed mt-4"
                            style="background-color: #6E49C7;"
                        >
                            <LoaderCircle
                                v-if="processing"
                                class="h-3 w-3 animate-spin mr-2"
                            />
                            <span v-if="!processing">Iniciar sesión</span>
                            <span v-else>Iniciando sesión...</span>
                        </Button>

                        <!-- Register Link -->
                        <div class="text-center pt-2">
                            <p class="text-xs text-gray-600">
                                ¿No tienes una cuenta?
                                <TextLink
                                    :href="register()"
                                    class="text-purple-600 hover:text-purple-700 font-medium underline underline-offset-4 transition-colors duration-200"
                                    :tabindex="6"
                                >
                                    Registrarse
                                </TextLink>
                            </p>
                        </div>
                    </Form>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.font-instrument {
    font-family: 'Instrument Sans', sans-serif;
}

/* Gradient text support */
.bg-clip-text {
    -webkit-background-clip: text;
    background-clip: text;
}

/* Enhanced form styling */
.space-y-3 > * + * {
    margin-top: 0.75rem;
}

/* Smooth transitions */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Focus states */
input:focus {
    outline: none;
    box-shadow: 0 0 0 2px #6E49C7;
    border-color: transparent;
}
</style>
