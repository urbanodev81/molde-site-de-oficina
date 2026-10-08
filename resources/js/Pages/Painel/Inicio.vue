<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, ExternalLink } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';

defineProps({
    kpis: { type: Object, required: true },
    avisos: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Painel" />

    <AuthenticatedLayout>
        <template #cabecalho>
            <p class="font-label text-label uppercase text-fg-subtle">Painel</p>
        </template>

        <PageHeader titulo="A galeria do site" descricao="O que está no ar hoje e o que falta.">
            <template #acoes>
                <a href="/galeria" target="_blank" rel="noopener" class="inline-flex min-h-[44px] items-center gap-2 text-sm font-semibold text-accent hover:underline">
                    Ver a galeria no site<ExternalLink class="h-4 w-4" aria-hidden="true" />
                </a>
            </template>
        </PageHeader>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard rotulo="Fotos no site" :valor="kpis.fotos" icone="image" />
            <StatCard rotulo="Vídeos no site" :valor="kpis.videos" icone="video" />
            <StatCard rotulo="Vídeos na página inicial" :valor="`${kpis.videosNaHome} de ${kpis.limiteNaHome}`" icone="home" />
        </div>

        <ul v-if="avisos.length" class="mt-6 grid gap-3">
            <li v-for="aviso in avisos" :key="aviso.chave" class="rounded-lg border border-line bg-surface p-4">
                <p class="text-sm font-semibold text-fg">{{ aviso.titulo }}</p>
                <p class="mt-1 text-sm text-fg-muted">{{ aviso.detalhe }}</p>
                <Link v-if="aviso.rota" :href="route(aviso.rota)" class="mt-2 inline-flex min-h-[44px] items-center gap-1 text-sm font-semibold text-accent hover:underline">
                    Resolver<ArrowRight class="h-4 w-4" aria-hidden="true" />
                </Link>
            </li>
        </ul>
    </AuthenticatedLayout>
</template>
