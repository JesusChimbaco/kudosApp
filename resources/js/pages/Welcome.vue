<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="KUDOS - Transforma tus hábitos">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
    </Head>
    <div class="min-h-screen bg-gradient-to-br from-white via-purple-50 to-emerald-50">
        <!-- Navigation -->
        <nav class="flex items-center justify-between p-6 lg:px-8">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-emerald-500 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-xl">K</span>
                </div>
                <span class="text-2xl font-bold text-gray-800 font-instrument">KUDOS</span>
            </div>
            
            <div class="flex items-center space-x-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="px-6 py-2.5 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors duration-200 shadow-sm"
                    style="background-color: #6E49C7;"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="px-4 py-2 text-gray-700 hover:text-purple-600 font-medium transition-colors duration-200"
                    >
                        Iniciar sesión
                    </Link>
                    <Link
                        :href="register()"
                        class="px-6 py-2.5 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors duration-200 shadow-sm"
                        style="background-color: #6E49C7;"
                    >
                        Registrarse
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="max-w-6xl mx-auto px-6 py-12 lg:py-20">
            <div class="text-center">
                <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 mb-6 font-instrument">
                    Transforma tus 
                    <span class="bg-gradient-to-r from-purple-600 to-emerald-500 bg-clip-text text-transparent">
                        hábitos
                    </span>
                </h1>
                <p class="text-xl lg:text-2xl text-gray-600 mb-12 max-w-3xl mx-auto leading-relaxed">
                    Construye rutinas poderosas, rastrea tu progreso y celebra cada pequeña victoria 
                    en tu camino hacia una mejor versión de ti mismo.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="px-8 py-4 bg-purple-600 text-white text-lg font-bold rounded-xl hover:bg-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                        style="background-color: #6E49C7;"
                    >
                        Comenzar Gratis
                    </Link>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600 mb-2">21</div>
                        <div class="text-gray-600">Días para formar un hábito</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-emerald-500 mb-2">∞</div>
                        <div class="text-gray-600">Posibilidades de crecimiento</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600 mb-2">1%</div>
                        <div class="text-gray-600">Mejor cada día</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="bg-white py-20">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6 font-instrument">
                        Herramientas que 
                        <span class="bg-gradient-to-r from-purple-600 to-emerald-500 bg-clip-text text-transparent">
                            impulsan
                        </span> 
                        tu éxito
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Todo lo que necesitas para construir hábitos duraderos y alcanzar tus metas
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-8 rounded-2xl">
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-600 to-purple-700 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 font-instrument">Seguimiento diario</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Registra tus hábitos fácilmente y observa tu progreso con visualizaciones intuitivas que te mantienen motivado.
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-8 rounded-2xl">
                        <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 font-instrument">Logros y recompensas</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Celebra tus victorias con un sistema de logros que reconoce tu dedicación y constancia.
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-8 rounded-2xl">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 font-instrument">Recordatorios inteligentes</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Nunca olvides tus hábitos con recordatorios personalizados que se adaptan a tu rutina diaria.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-purple-600 to-emerald-500 py-20" style="background: linear-gradient(135deg, #6E49C7 0%, #10B981 100%);">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6 font-instrument">
                    ¿Listo para cambiar tu vida?
                </h2>
                <Link
                    v-if="!$page.props.auth.user"
                    :href="register()"
                    class="inline-block px-10 py-4 bg-white text-purple-600 text-lg font-bold rounded-xl hover:bg-gray-100 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                >
                    Comenzar mi transformación
                </Link>
                <Link
                    v-else
                    :href="dashboard()"
                    class="inline-block px-10 py-4 bg-white text-purple-600 text-lg font-bold rounded-xl hover:bg-gray-100 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                >
                    Continuar mi progreso
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-50 py-8">
            <div class="max-w-6xl mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center space-x-3 mb-4 md:mb-0">
                        <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-emerald-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold">K</span>
                        </div>
                        <span class="text-xl font-bold text-gray-800 font-instrument">KUDOS</span>
                    </div>
                    <p class="text-gray-600 text-sm">
                        © 2025 KUDOS. Construyendo mejores hábitos, un día a la vez.
                    </p>
                </div>
            </div>
        </footer>
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

/* Smooth transitions */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>
