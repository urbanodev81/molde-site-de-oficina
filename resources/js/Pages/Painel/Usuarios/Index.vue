<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ArchiveRestore, Plus } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TwoStepConfirm from '@/Components/TwoStepConfirm.vue';
import Chip from '@/Components/Chip.vue';
import Checkbox from '@/Components/Checkbox.vue';
import EstadoVazio from '@/Components/EstadoVazio.vue';
import AbasDeArquivados from '@/Components/AbasDeArquivados.vue';
import BarraDeAcoesEmLote from '@/Components/BarraDeAcoesEmLote.vue';
import { useListaEmLote } from '@/Composables/useListaEmLote';

const props = defineProps({
    usuarios: { type: Array, default: () => [] },

    arquivadas: { type: Boolean, default: false },
    totais: { type: Object, default: () => ({ ativas: 0, arquivadas: 0 }) },
    podeGerenciar: { type: Boolean, default: false },
});

const { selecao, acoes, emLote, nome } = useListaEmLote({
    slug: 'usuarios',
    ids: () => props.usuarios.filter((u) => !u.euMesmo).map((u) => u.uuid),
    podeGerenciar: () => props.podeGerenciar,
    arquivados: () => props.arquivadas,
    nome: ['conta', 'contas', 'a'],
    liga: ['Ativar', 'Desativar'],
});
</script>

<template>
    <Head title="Contas de acesso" />

    <AuthenticatedLayout>
        <template #cabecalho><p class="font-label text-label uppercase text-fg-subtle">Administração</p></template>

        <PageHeader
            titulo="Contas de acesso"
            descricao="Não existe auto-cadastro: conta aqui nasce criada por quem já está dentro."
        >
            <template #acoes>
                <Link v-if="podeGerenciar" :href="route('painel.usuarios.create')">
                    <PrimaryButton type="button"><Plus class="h-4 w-4" aria-hidden="true" />Nova conta</PrimaryButton>
                </Link>
            </template>
        </PageHeader>

        <AbasDeArquivados rota="painel.usuarios.index" parametro="arquivadas" :aberta="arquivadas" :rotulos="['Com acesso', 'Arquivadas']" :totais="[totais.ativas, totais.arquivadas]" descricao="Contas com acesso ou arquivadas" />

        <label v-if="usuarios.some((u) => !u.euMesmo)" class="mb-3 inline-flex min-h-[44px] items-center gap-2 text-sm text-fg">
            <Checkbox :checked="selecao.todosMarcados.value" @update:checked="selecao.alternarTodos()" />Marcar todas
        </label>

        <BarraDeAcoesEmLote :quantidade="selecao.selecionados.value.length" :acoes="acoes" :nome="nome" @acao="emLote($event.key)" @limpar="selecao.limpar()" />

        <EstadoVazio v-if="!usuarios.length" icone="users" titulo="Nenhuma conta arquivada" descricao="Conta arquivada perde o acesso e fica guardada aqui até alguém restaurar." />

        <div v-else class="overflow-hidden rounded-lg border border-line bg-surface">
            <ul class="divide-y divide-line">
                <li v-for="usuario in usuarios" :key="usuario.uuid" class="flex flex-wrap items-center gap-4 px-4 py-4 sm:px-5" :class="selecao.marcado(usuario.uuid) ? 'bg-accent-subtle' : ''">
                    <label v-if="!usuario.euMesmo" class="flex h-11 w-11 shrink-0 items-center justify-center">
                        <Checkbox :checked="selecao.marcado(usuario.uuid)" @update:checked="selecao.alternar(usuario.uuid)" />
                        <span class="sr-only">Marcar {{ usuario.nome }}</span>
                    </label>
                    <span v-else class="h-11 w-11 shrink-0" aria-hidden="true" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-fg">
                            {{ usuario.nome }}
                            <span v-if="usuario.euMesmo" class="font-normal text-fg-subtle">(você)</span>
                        </p>
                        <p class="truncate text-sm text-fg-muted">{{ usuario.email }}</p>
                        <p class="text-xs text-fg-subtle">
                            {{ usuario.ultimoAcesso ? `Último acesso em ${usuario.ultimoAcesso}` : 'Nunca entrou' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Chip v-for="perfil in usuario.perfis" :key="perfil" :rotulo="perfil" token="info" />
                        <Chip v-if="!usuario.ativo" rotulo="Inativa" token="danger" icone="circle-slash" />
                    </div>

                    <div v-if="podeGerenciar && !usuario.arquivada" class="flex flex-wrap gap-2">
                        <Link :href="route('painel.usuarios.edit', usuario.uuid)"><SecondaryButton type="button">Editar</SecondaryButton></Link>
                        <TwoStepConfirm v-if="!usuario.euMesmo" rotulo="Arquivar" verbo="Arquivar" :nome="usuario.nome" @confirmar="router.delete(route('painel.usuarios.destroy', usuario.uuid))" />
                    </div>
                    <SecondaryButton v-else-if="podeGerenciar" type="button" @click="emLote('restaurar', [usuario.uuid])">
                        <ArchiveRestore class="h-4 w-4" aria-hidden="true" />Restaurar
                    </SecondaryButton>
                </li>
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
