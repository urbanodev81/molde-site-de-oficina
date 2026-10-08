<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Video, Image, UserCog, ScrollText, LayoutDashboard, Store, X,
} from 'lucide-vue-next';

defineProps({ aberta: Boolean });
defineEmits(['fechar']);

const pagina = usePage();
const permissoes = computed(() => pagina.props.auth?.permissoes ?? {});

const grupos = [
    {
        titulo: null,
        itens: [
            { rotulo: 'Início', rota: 'painel.inicio', icone: LayoutDashboard, permissao: null },
        ],
    },
    {
        titulo: 'Galeria do site',
        itens: [
            { rotulo: 'Fotos', rota: 'painel.fotos.index', icone: Image, permissao: 'fotos' },
            { rotulo: 'Vídeos', rota: 'painel.videos.index', icone: Video, permissao: 'videos' },
        ],
    },
    {
        titulo: 'Site',
        itens: [
            { rotulo: 'Dados da oficina', rota: 'painel.oficina.edit', icone: Store, permissao: 'oficina' },
        ],
    },
    {
        titulo: 'Administração',
        itens: [
            { rotulo: 'Contas de acesso', rota: 'painel.usuarios.index', icone: UserCog, permissao: 'usuarios' },
            { rotulo: 'Trilha de alterações', rota: 'painel.auditoria.index', icone: ScrollText, permissao: 'auditoria' },
        ],
    },
];

const visiveis = computed(() =>
    grupos
        .map((grupo) => ({
            ...grupo,
            itens: grupo.itens.filter((item) => item.permissao === null || permissoes.value[item.permissao]),
        }))
        .filter((grupo) => grupo.itens.length > 0),
);

const ativo = (rota) => route().current(rota) || route().current(rota.replace(/\.\w+$/, '.*'));
</script>

<template>

    <div
        v-if="aberta"
        class="fixed inset-0 z-40 bg-overlay/60 backdrop-blur-sm lg:hidden"
        @click="$emit('fechar')"
    />

    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-line bg-surface
               transition-transform lg:translate-x-0"
        :class="aberta ? 'translate-x-0' : '-translate-x-full'"
        aria-label="Menu do painel"
    >
        <div class="flex items-center justify-between gap-2 border-b border-line px-5 py-4">
            <Link :href="route('painel.inicio')" class="min-w-0">
                <p class="truncate font-display text-lg font-black uppercase leading-none text-fg">
                    A melhor oficina
                </p>
                <p class="mt-1 font-label text-label uppercase text-accent">Painel</p>
            </Link>

            <button
                type="button"
                class="grid h-11 w-11 place-items-center rounded-md text-fg-muted lg:hidden"
                aria-label="Fechar menu"
                @click="$emit('fechar')"
            >
                <X class="h-5 w-5" aria-hidden="true" />
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <div v-for="grupo in visiveis" :key="grupo.titulo ?? 'raiz'" class="mb-5">
                <p v-if="grupo.titulo" class="mb-2 px-2 font-label text-label uppercase text-fg-subtle">
                    {{ grupo.titulo }}
                </p>

                <ul class="space-y-1">
                    <li v-for="item in grupo.itens" :key="item.rota">
                        <Link
                            :href="route(item.rota)"
                            class="flex min-h-[44px] items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition"
                            :class="ativo(item.rota)
                                ? 'bg-accent-subtle text-accent'
                                : 'text-fg-muted hover:bg-surface-sunken hover:text-fg'"
                            :aria-current="ativo(item.rota) ? 'page' : undefined"
                            @click="$emit('fechar')"
                        >
                            <component :is="item.icone" class="h-5 w-5 shrink-0" aria-hidden="true" />
                            {{ item.rotulo }}
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="border-t border-line px-5 py-3">
            <a
                :href="route('site.home')"
                target="_blank"
                rel="noopener"
                class="font-label text-label uppercase text-fg-subtle transition hover:text-accent"
            >
                Ver o site ↗
            </a>
        </div>
    </aside>
</template>
