<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Archive, ArchiveRestore, Eye, EyeOff, Printer, Send, Upload } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import EstadoVazio from '@/Components/EstadoVazio.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TwoStepConfirm from '@/Components/TwoStepConfirm.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Chip from '@/Components/Chip.vue';
import BarraDeAcoesEmLote from '@/Components/BarraDeAcoesEmLote.vue';
import ModalEnviarLista from '@/Components/ModalEnviarLista.vue';
import Paginacao from '@/Components/Paginacao.vue';
import { useSelecaoEmLote } from '@/Composables/useSelecaoEmLote';

const props = defineProps({
    fotos: { type: Object, required: true },

    arquivadas: { type: Boolean, default: false },
    totais: { type: Object, default: () => ({ ativas: 0, arquivadas: 0 }) },
    podeGerenciar: { type: Boolean, default: false },
});

const form = useForm({ arquivo: null, titulo: '', descricao: '', ordem: 0, publicada: true });
const campoArquivo = ref(null);

function enviar() {
    form.post(route('painel.fotos.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();

            if (campoArquivo.value) campoArquivo.value.value = '';
        },
    });
}

const editando = ref(null);
const edicao = useForm({ titulo: '', descricao: '', ordem: 0, publicada: true });

function abrirEdicao(foto) {
    editando.value = foto.uuid;
    Object.assign(edicao, {
        titulo: foto.titulo ?? '',
        descricao: foto.descricao ?? '',
        ordem: foto.ordem,
        publicada: foto.publicada,
    });
}

const selecao = useSelecaoEmLote(() => props.fotos.data.map((f) => f.uuid));
const enviando = ref(false);

const acoes = computed(() => {
    const imprimir = { key: 'imprimir', label: 'Imprimir', icon: Printer };

    if (!props.podeGerenciar) return [imprimir];

    if (props.arquivadas) {
        return [{ key: 'restaurar', label: 'Restaurar', icon: ArchiveRestore }, imprimir];
    }

    return [
        { key: 'publicar', label: 'Publicar', icon: Eye },
        { key: 'despublicar', label: 'Tirar do site', icon: EyeOff },
        { key: 'arquivar', label: 'Arquivar', icon: Archive, confirmar: 'Arquivar as fotos marcadas? Elas saem do site e ficam na aba Arquivadas.' },
        imprimir,
        { key: 'enviar', label: 'Enviar por e-mail', icon: Send },
    ];
});

function emLote(acao, ids = selecao.selecionados.value) {
    if (acao === 'imprimir') {
        window.open(route('painel.fotos.imprimir', { ids }), '_blank', 'noopener');
        return;
    }

    if (acao === 'enviar') {
        enviando.value = true;
        return;
    }

    router.post(route('painel.fotos.lote'), { acao, ids: [...ids] }, { preserveScroll: true, onSuccess: selecao.limpar });
}

const abaAtiva = 'bg-accent text-fg-on-accent';
const abaInativa = 'text-fg-muted hover:bg-surface-sunken';

const campoTexto = 'w-full rounded-md border-line-input bg-surface text-sm text-fg focus:border-line-strong focus:ring-2 focus:ring-accent-ring';
</script>

<template>
    <Head title="Fotos" />

    <AuthenticatedLayout>
        <template #cabecalho><p class="font-label text-label uppercase text-fg-subtle">Galeria do site</p></template>

        <PageHeader titulo="Fotos" descricao="O serviço feito, com a explicação do que foi trocado e por quê. Cada foto aparece na página Galeria do site." />

        <form v-if="podeGerenciar && !arquivadas" class="mb-6 rounded-lg border border-line bg-surface p-5" @submit.prevent="enviar">
            <h2 class="mb-4 font-display text-xl font-bold uppercase text-fg">Enviar foto</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="foto-arquivo" value="Arquivo" obrigatorio />
                    <input id="foto-arquivo" ref="campoArquivo" type="file" accept="image/jpeg,image/png,image/webp" class="w-full text-sm text-fg-muted file:mr-3 file:rounded-md file:border-0 file:bg-accent-subtle file:px-4 file:py-2 file:text-sm file:font-semibold file:text-accent" @input="form.arquivo = $event.target.files[0]" />
                    <p class="mt-1 text-xs text-fg-subtle">JPG, PNG ou WebP, até 8 MB.</p>
                    <InputError :message="form.errors.arquivo" />
                </div>
                <div>
                    <InputLabel for="foto-titulo" value="Título" obrigatorio />
                    <TextInput id="foto-titulo" v-model="form.titulo" maxlength="120" placeholder="Ex.: Troca de pastilhas e discos" />
                    <InputError :message="form.errors.titulo" />
                </div>
                <div class="sm:col-span-2">
                    <InputLabel for="foto-descricao" value="Descrição" />
                    <textarea id="foto-descricao" v-model="form.descricao" rows="2" maxlength="2000" :class="campoTexto" />
                    <p class="mt-1 text-xs text-fg-subtle">O que foi feito no carro. Aparece embaixo da foto e quando ela abre grande.</p>
                    <InputError :message="form.errors.descricao" />
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-4">
                <PrimaryButton :disabled="form.processing"><Upload class="h-4 w-4" aria-hidden="true" />Enviar</PrimaryButton>
                <label class="flex items-center gap-2 text-sm text-fg"><Checkbox v-model:checked="form.publicada" />Publicada no site</label>
            </div>
        </form>

        <nav class="mb-4 flex flex-wrap gap-1" aria-label="Fotos na galeria ou arquivadas">
            <Link :href="route('painel.fotos.index')" class="flex min-h-[44px] items-center gap-2 rounded-md px-4 text-sm font-semibold" :class="arquivadas ? abaInativa : abaAtiva" :aria-current="arquivadas ? undefined : 'page'">
                Na galeria ({{ totais.ativas }})
            </Link>
            <Link :href="route('painel.fotos.index', { arquivadas: 1 })" class="flex min-h-[44px] items-center gap-2 rounded-md px-4 text-sm font-semibold" :class="arquivadas ? abaAtiva : abaInativa" :aria-current="arquivadas ? 'page' : undefined">
                <Archive class="h-4 w-4" aria-hidden="true" />Arquivadas ({{ totais.arquivadas }})
            </Link>
        </nav>

        <label v-if="fotos.data.length" class="mb-3 inline-flex min-h-[44px] items-center gap-2 text-sm text-fg">
            <Checkbox :checked="selecao.todosMarcados.value" @update:checked="selecao.alternarTodos()" />Marcar as {{ fotos.data.length }} desta página
        </label>

        <BarraDeAcoesEmLote :quantidade="selecao.selecionados.value.length" :acoes="acoes" :nome="['foto', 'fotos']" @acao="emLote($event.key)" @limpar="selecao.limpar()" />

        <div v-if="fotos.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <figure v-for="foto in fotos.data" :key="foto.uuid" class="relative overflow-hidden rounded-lg border bg-surface" :class="selecao.marcado(foto.uuid) ? 'border-line-strong ring-2 ring-accent-ring' : 'border-line'">

                <label class="absolute left-2 top-2 flex h-11 w-11 items-center justify-center rounded-md bg-surface/90">
                    <Checkbox :checked="selecao.marcado(foto.uuid)" @update:checked="selecao.alternar(foto.uuid)" />
                    <span class="sr-only">Marcar {{ foto.titulo }}</span>
                </label>
                <img :src="foto.url" :alt="foto.titulo" class="aspect-[4/3] w-full object-cover" loading="lazy" />

                <figcaption v-if="editando !== foto.uuid" class="p-4">
                    <p class="truncate text-sm font-semibold text-fg">{{ foto.titulo }}</p>
                    <p v-if="foto.descricao" class="line-clamp-2 text-xs text-fg-subtle">{{ foto.descricao }}</p>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <Chip v-if="foto.exemplo" rotulo="Exemplo" token="warning" />
                            <EyeOff v-if="!foto.publicada" class="h-4 w-4 text-fg-subtle" aria-label="Não publicada" />
                        </div>
                        <div v-if="podeGerenciar && !foto.arquivada" class="flex gap-2">
                            <SecondaryButton type="button" @click="abrirEdicao(foto)">Editar</SecondaryButton>
                            <TwoStepConfirm rotulo="Arquivar" verbo="Arquivar" :nome="foto.titulo" @confirmar="router.delete(route('painel.fotos.destroy', foto.uuid), { preserveScroll: true })" />
                        </div>
                        <SecondaryButton v-else-if="podeGerenciar" type="button" @click="emLote('restaurar', [foto.uuid])">
                            <ArchiveRestore class="h-4 w-4" aria-hidden="true" />Restaurar
                        </SecondaryButton>
                    </div>
                </figcaption>

                <form v-else class="grid gap-3 p-4" @submit.prevent="edicao.put(route('painel.fotos.update', foto.uuid), { preserveScroll: true, onSuccess: () => (editando = null) })">
                    <TextInput v-model="edicao.titulo" aria-label="Título" placeholder="Título" maxlength="120" />
                    <textarea v-model="edicao.descricao" rows="3" maxlength="2000" :class="campoTexto" aria-label="Descrição" placeholder="Descrição" />
                    <div>
                        <InputLabel :for="`ordem-${foto.uuid}`" value="Ordem" />
                        <TextInput :id="`ordem-${foto.uuid}`" v-model="edicao.ordem" type="number" min="0" max="9999" />
                        <p class="mt-1 text-xs text-fg-subtle">Número menor aparece primeiro.</p>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-fg"><Checkbox v-model:checked="edicao.publicada" />Publicada no site</label>
                    <InputError :message="edicao.errors.titulo || edicao.errors.descricao || edicao.errors.ordem" />
                    <div class="flex gap-2">
                        <PrimaryButton :disabled="edicao.processing">Salvar</PrimaryButton>
                        <SecondaryButton type="button" @click="editando = null">Cancelar</SecondaryButton>
                    </div>
                </form>
            </figure>
        </div>

        <EstadoVazio v-else-if="arquivadas" icone="image" titulo="Nenhuma foto arquivada" descricao="Foto arquivada sai da galeria e do site, e fica guardada aqui até alguém restaurar." />
        <EstadoVazio v-else icone="image" titulo="Nenhuma foto ainda" descricao="A primeira foto enviada já aparece na página Galeria do site." />

        <Paginacao :links="fotos.links" />

        <ModalEnviarLista
            :show="enviando"
            :ids="selecao.selecionados.value"
            :rota="route('painel.fotos.lote.enviar')"
            :nome="['foto', 'fotos']"
            explicacao="Vai o link de cada foto publicada no site; as que não estão publicadas ficam de fora."
            @enviado="selecao.limpar()"
            @close="enviando = false"
        />
    </AuthenticatedLayout>
</template>
