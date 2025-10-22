<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { login } from '@/routes';
import { Form, Head, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
</script>

<template>
    <Head title="Registrarse - KUDOS">
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
                        Comienza tu transformación
                    </h1>
                    <p class="text-purple-100 mb-5 text-xs leading-relaxed">
                        Únete a miles de personas que están construyendo mejores hábitos con KUDOS.
                    </p>

                    <!-- Benefits -->
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path>
                                </svg>
                            </div>
                            <span class="text-purple-100 text-xs">Seguimiento visual</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path>
                                </svg>
                            </div>
                            <span class="text-purple-100 text-xs">Sistema de logros</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path>
                                </svg>
                            </div>
                            <span class="text-purple-100 text-xs">Recordatorios personalizados</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Registration Form -->
            <div class="w-1/2 p-6 flex flex-col justify-center">
                <div class="w-full max-w-xs mx-auto">
                    <!-- Form Header -->
                    <div class="text-center mb-4">
                        <h2 class="text-xl font-bold text-gray-900 mb-1 font-instrument">Crear cuenta</h2>
                        <p class="text-xs text-gray-600">Ingresa tus datos para comenzar</p>
                    </div>

                    <Form
                        v-bind="RegisteredUserController.store.form()"
                        :reset-on-success="['password', 'password_confirmation']"
                        v-slot="{ errors, processing }"
                        class="space-y-2"
                    >
                        <!-- Full Name -->
                        <div>
                            <Label for="name" class="text-xs font-medium text-gray-700 mb-1 block">Nombre completo</Label>
                            <Input
                                id="name"
                                type="text"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="name"
                                name="name"
                                placeholder="Juan Pérez"
                                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            />
                            <InputError :message="errors.name" class="text-red-500 text-xs mt-1" />
                        </div>

                        <!-- Display Name -->
                        <div>
                            <Label for="nombre" class="text-xs font-medium text-gray-700 mb-1 block">Nombre de usuario</Label>
                            <Input
                                id="nombre"
                                type="text"
                                required
                                :tabindex="2"
                                autocomplete="nickname"
                                name="nombre"
                                placeholder="¿Cómo te llamamos?"
                                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            />
                            <InputError :message="errors.nombre" class="text-red-500 text-xs mt-1" />
                        </div>

                        <!-- Email -->
                        <div>
                            <Label for="email" class="text-xs font-medium text-gray-700 mb-1 block">Correo electrónico</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                :tabindex="3"
                                autocomplete="email"
                                name="email"
                                placeholder="tu-email@ejemplo.com"
                                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            />
                            <InputError :message="errors.email" class="text-red-500 text-xs mt-1" />
                        </div>

                        <!-- Password -->
                        <div>
                            <Label for="password" class="text-xs font-medium text-gray-700 mb-1 block">Contraseña</Label>
                            <Input
                                id="password"
                                type="password"
                                required
                                :tabindex="4"
                                autocomplete="new-password"
                                name="password"
                                placeholder="Mínimo 8 caracteres"
                                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            />
                            <InputError :message="errors.password" class="text-red-500 text-xs mt-1" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <Label for="password_confirmation" class="text-xs font-medium text-gray-700 mb-1 block">Confirmar contraseña</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                required
                                :tabindex="5"
                                autocomplete="new-password"
                                name="password_confirmation"
                                placeholder="Repite tu contraseña"
                                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            />
                            <InputError :message="errors.password_confirmation" class="text-red-500 text-xs mt-1" />
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            tabindex="6"
                            :disabled="processing"
                            data-test="register-user-button"
                            class="w-full py-2 px-4 bg-purple-600 text-white font-medium text-xs rounded-md hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed mt-3"
                            style="background-color: #6E49C7;"
                        >
                            <LoaderCircle
                                v-if="processing"
                                class="h-3 w-3 animate-spin mr-2"
                            />
                            <span v-if="!processing">Crear mi cuenta</span>
                            <span v-else>Creando cuenta...</span>
                        </Button>

                        <!-- Login Link -->
                        <div class="text-center pt-2">
                            <p class="text-xs text-gray-600">
                                ¿Ya tienes una cuenta?
                                <TextLink
                                    :href="login()"
                                    class="text-purple-600 hover:text-purple-700 font-medium underline underline-offset-4 transition-colors duration-200"
                                    :tabindex="7"
                                >
                                    Iniciar sesión
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
.space-y-2 > * + * {
    margin-top: 0.5rem;
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
