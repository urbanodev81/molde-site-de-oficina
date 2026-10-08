<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArchiveRestore, Plus, EyeOff, Home } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import EstadoVazio from '@/Components/EstadoVazio.vue';
import Paginacao from '@/Components/Paginacao.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TwoStepConfirm from '@/Components/TwoStepConfirm.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Chip from '@/Components/Chip.vue';
import AbasDeArquivados from '@/Components/AbasDeArquivados.vue';
import BarraDeAcoesEmLote from '@/Components/BarraDeAcoesEmLote.vue';
import ModalEnviarLista from '@/Components/ModalEnviarLista.vue';
import { useListaEmLote } from '@/Composables/useListaEmLote';

const props = defineProps({
    videos: { type: Object, required: true },
    provedores: { type: Array, default: () => [] },
    limiteNaHome: { type: Number, default: 2 },
    naHome: { type: Number, default: 0 },

    arquivados: { type: Boolean, default: false },
    totais: { type: Object, default: () => ({ ativos: 0, arquivados: 0 }) },
    podeGerenciar: { type: Boolean, default: false },
});

const { selecao, acoes, emLote, enviando, nome } = useListaEmLote({
    slug: 'videos',
    ids: () => props.videos.data.map((v) => v.uuid),
    podeGerenciar: () => props.podeGerenciar,
    arquivados: () => props.arquivados,
    nome: ['vídeo', 'vídeos', 'o'],
    envia: true,
});

const form = useForm({ link: '', titulo: '', descricao: '', na_home: false, ordem: 0, publicado: true });

const editando = ref(null);
const edicao = useForm({ link: '', titulo: '', descricao: '', na_home: false, ordem: 0, publicado: true });

function abrirEdicao(video) {
    editando.value = video.uuid;
    Object.assign(edicao, {

        link: '',
        titulo: video.titulo ?? '',
        descricao: video.descricao ?? '',
        na_home: video.na_home,
        ordem: video.ordem,
        publicado: video.publicado,
    });
}

const campoTexto = 'w-full rounded-md border-line-input bg-surface text-sm text-fg focus:border-line-strong focus:ring-2 focus:ring-accent-ring';
</script>

<template>
    <Head title="Vídeos" />

    <AuthenticatedLayout>
        <template #cabecalho><p class="font-label text-label uppercase text-fg-subtle">Galeria do site</p></template>

        <PageHeader titulo="Vídeos" descricao="O vídeo fica onde foi publicado; aqui você cola o link ou o código de incorporação e ele abre grande dentro do site." />

        <div class="mb-6 rounded-lg border border-line bg-surface-sunken p-4 text-sm text-fg-muted">
            <p>
                Aceita vídeo do {{ provedores.join(', ') }}. A página inicial mostra até
                <strong class="text-fg">{{ limiteNaHome }}</strong> vídeos marcados como "Na página inicial"; os outros ficam na página Galeria.
                <template v-if="naHome > limiteNaHome">
                    <strong class="text-fg">Há {{ naHome }} marcados:</strong> só os {{ limiteNaHome }} primeiros, pela ordem, aparecem lá.
                </template>
            </p>
        </div>

        <form v-if="podeGerenciar && !arquivados" class="mb-6 rounded-lg border border-line bg-surface p-5" @submit.prevent="form.post(route('painel.videos.store'), { preserveScroll: true, onSuccess: () => form.reset() })">
            <h2 class="mb-4 font-display text-xl font-bold uppercase text-fg">Incluir vídeo</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <InputLabel for="video-link" value="Link ou código de incorporação" obrigatorio />
                    <textarea id="video-link" v-model="form.link" rows="2" maxlength="2000" :class="campoTexto" placeholder="https://www.youtube.com/watch?v=…  ou  <iframe src=&quot;…&quot;></iframe>" />
                    <p class="mt-1 text-xs text-fg-subtle">No YouTube: Compartilhar → Copiar link, ou Compartilhar → Incorporar e copie o código inteiro.</p>
                    <InputError :message="form.errors.link" />
                </div>
                <div>
                    <InputLabel for="video-titulo" value="Título" obrigatorio />
                    <TextInput id="video-titulo" v-model="form.titulo" maxlength="120" placeholder="Ex.: Troca da correia dentada" />
                    <InputError :message="form.errors.titulo" />
                </div>
                <div>
                    <InputLabel for="video-descricao" value="Descrição" />
                    <textarea id="video-descricao" v-model="form.descricao" rows="2" maxlength="2000" :class="campoTexto" />
                    <InputError :message="form.errors.descricao" />
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-4">
                <PrimaryButton :disabled="form.processing"><Plus class="h-4 w-4" aria-hidden="true" />Incluir</PrimaryButton>
                <label class="flex items-center gap-2 text-sm text-fg"><Checkbox v-model:checked="form.publicado" />Publicado no site</label>
                <label class="flex items-center gap-2 text-sm text-fg"><Checkbox v-model:checked="form.na_home" />Na página inicial</label>
            </div>
        </form>

        <AbasDeArquivados rota="painel.videos.index" :aberta="arquivados" :rotulos="['Na galeria', 'Arquivados']" :totais="[totais.ativos, totais.arquivados]" descricao="Vídeos na galeria ou arquivados" />

        <label v-if="videos.data.length" class="mb-3 inline-flex min-h-[44px] items-center gap-2 text-sm text-fg">
            <Checkbox :checked="selecao.todosMarcados.value" @update:checked="selecao.alternarTodos()" />Marcar os {{ videos.data.length }} desta página
        </label>

        <BarraDeAcoesEmLote :quantidade="selecao.selecionados.value.length" :acoes="acoes" :nome="nome" @acao="emLote($event.key)" @limpar="selecao.limpar()" />

        <div v-if="videos.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article v-for="video in videos.data" :key="video.uuid" class="relative overflow-hidden rounded-lg border bg-surface" :class="selecao.marcado(video.uuid) ? 'border-line-strong ring-2 ring-accent-ring' : 'border-line'">

                <label class="absolute left-2 top-2 flex h-11 w-11 items-center justify-center rounded-md bg-surface/90">
                    <Checkbox :checked="selecao.marcado(video.uuid)" @update:checked="selecao.alternar(video.uuid)" />
                    <span class="sr-only">Marcar {{ video.titulo }}</span>
                </label>
                <img :src="video.capa" alt="" class="aspect-video w-full object-cover" loading="lazy" />

                <div v-if="editando !== video.uuid" class="p-4">
                    <h2 class="truncate text-sm font-semibold text-fg">{{ video.titulo }}</h2>
                    <p class="truncate text-xs text-fg-subtle">
                        <a :href="video.link" target="_blank" rel="noopener" class="text-accent hover:underline">{{ video.provedor }}</a>
                        <template v-if="video.descricao"> · {{ video.descricao }}</template>
                    </p>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <Chip v-if="video.exemplo" rotulo="Exemplo" token="warning" />
                            <Home v-if="video.na_home" class="h-4 w-4 text-accent" aria-label="Na página inicial" />
                            <EyeOff v-if="!video.publicado" class="h-4 w-4 text-fg-subtle" aria-label="Não publicado" />
                        </div>
                        <div v-if="podeGerenciar && !video.arquivado" class="flex gap-2">
                            <SecondaryButton type="button" @click="abrirEdicao(video)">Editar</SecondaryButton>
                            <TwoStepConfirm rotulo="Arquivar" verbo="Arquivar" :nome="video.titulo" @confirmar="router.delete(route('painel.videos.destroy', video.uuid), { preserveScroll: true })" />
                        </div>
                        <SecondaryButton v-else-if="podeGerenciar" type="button" @click="emLote('restaurar', [video.uuid])">
                            <ArchiveRestore class="h-4 w-4" aria-hidden="true" />Restaurar
                        </SecondaryButton>
                    </div>
                </div>

                <form v-else class="grid gap-3 p-4" @submit.prevent="edicao.put(route('painel.videos.update', video.uuid), { preserveScroll: true, onSuccess: () => (editando = null) })">
                    <TextInput v-model="edicao.titulo" aria-label="Título" placeholder="Título" maxlength="120" />
                    <textarea v-model="edicao.descricao" rows="3" maxlength="2000" :class="campoTexto" aria-label="Descrição" placeholder="Descrição" />
                    <textarea v-model="edicao.link" rows="2" maxlength="2000" :class="campoTexto" aria-label="Novo link ou código de incorporação" placeholder="Novo link (deixe em branco para manter o vídeo)" />
                    <div>
                        <InputLabel :for="`ordem-${video.uuid}`" value="Ordem" />
                        <TextInput :id="`ordem-${video.uuid}`" v-model="edicao.ordem" type="number" min="0" max="9999" />
                        <p class="mt-1 text-xs text-fg-subtle">Número menor aparece primeiro.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-fg"><Checkbox v-model:checked="edicao.publicado" />Publicado</label>
                        <label class="flex items-center gap-2 text-sm text-fg"><Checkbox v-model:checked="edicao.na_home" />Na página inicial</label>
                    </div>
                    <InputError :message="edicao.errors.link || edicao.errors.titulo || edicao.errors.descricao || edicao.errors.ordem" />
                    <div class="flex gap-2">
                        <PrimaryButton :disabled="edicao.processing">Salvar</PrimaryButton>
                        <SecondaryButton type="button" @click="editando = null">Cancelar</SecondaryButton>
                    </div>
                </form>
            </article>
        </div>

        <EstadoVazio v-else-if="arquivados" icone="video" titulo="Nenhum vídeo arquivado" descricao="Vídeo arquivado sai da galeria e do site, e fica guardado aqui até alguém restaurar." />
        <EstadoVazio v-else icone="video" titulo="Nenhum vídeo ainda" descricao="Cole o link de um vídeo da oficina para ele aparecer no site." />

        <Paginacao :links="videos.links" />

        <ModalEnviarLista
            :show="enviando"
            :ids="selecao.selecionados.value"
            :rota="route('painel.videos.lote.enviar')"
            :nome="nome"
            explicacao="Vai o link de cada vídeo publicado no site; os que não estão publicados ficam de fora."
            @enviado="selecao.limpar()"
            @close="enviando = false"
        />
    </AuthenticatedLayout>
</template>
