<script setup>

import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Chip from '@/Components/Chip.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    oficina: { type: Object, required: true },
    horarioInformado: { type: Boolean, default: false },
    semana: { type: Array, required: true },
    emUso: { type: Object, required: true },
    exemplos: { type: Object, required: true },
    podeGerenciar: { type: Boolean, default: false },
});

const form = useForm({
    whatsapp: props.oficina.whatsapp ?? '',
    telefone: props.oficina.telefone ?? '',
    email: props.oficina.email ?? '',
    instagram: props.oficina.instagram ?? '',
    logradouro: props.oficina.logradouro ?? '',
    bairro: props.oficina.bairro ?? '',
    cidade: props.oficina.cidade ?? '',
    uf: props.oficina.uf ?? '',
    cep: props.oficina.cep ?? '',
    latitude: props.oficina.latitude ?? '',
    longitude: props.oficina.longitude ?? '',
    mapa: props.oficina.mapa ?? '',
    horario_informado: props.horarioInformado,
    semana: props.semana.map((d) => ({ ...d })),
});

const campoTexto = 'w-full rounded-md border-line-input bg-surface text-sm text-fg focus:border-line-strong focus:ring-2 focus:ring-accent-ring';
const campoHora = 'rounded-md border-line-input bg-surface text-sm text-fg focus:border-line-strong focus:ring-2 focus:ring-accent-ring disabled:opacity-40';

const salvar = () => form.put(route('painel.oficina.update'), { preserveScroll: true });
</script>

<template>
    <Head title="Dados da oficina" />

    <AuthenticatedLayout>
        <template #cabecalho><p class="font-label text-label uppercase text-fg-subtle">Site</p></template>

        <PageHeader titulo="Dados da oficina" />

        <p class="mb-6 max-w-2xl text-sm text-fg-muted">
            O que está aqui aparece no site: botões de WhatsApp, rodapé, a seção "Onde estamos" e o que o Google lê.
            Campo em branco continua mostrando o dado de exemplo, com a etiqueta "exemplo", até você preencher.
        </p>

        <form class="max-w-3xl space-y-6" @submit.prevent="salvar">
            <fieldset :disabled="!podeGerenciar" class="space-y-6">
                <section class="rounded-lg border border-line bg-surface p-5">
                    <h2 class="mb-4 font-display text-xl font-bold uppercase text-fg">Contato</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="of-whatsapp" value="WhatsApp" />
                            <TextInput id="of-whatsapp" v-model="form.whatsapp" inputmode="tel" placeholder="(11) 90000-0000" />
                            <p class="mt-1 text-xs text-fg-subtle">No site hoje: {{ emUso.whatsapp }}. Em branco mantém este.</p>
                            <InputError :message="form.errors.whatsapp" />
                        </div>
                        <div>
                            <InputLabel for="of-telefone" value="Telefone para ligar" />
                            <TextInput id="of-telefone" v-model="form.telefone" inputmode="tel" placeholder="(11) 2909-0000" />
                            <p class="mt-1 text-xs text-fg-subtle">Em branco, "Ligar para a oficina" chama o número do WhatsApp.</p>
                            <InputError :message="form.errors.telefone" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2"><InputLabel for="of-email" value="E-mail" /><Chip v-if="exemplos.email" rotulo="Exemplo no site" token="warning" /></div>
                            <TextInput id="of-email" v-model="form.email" type="email" :placeholder="emUso.email" />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2"><InputLabel for="of-instagram" value="Instagram" /><Chip v-if="exemplos.instagram" rotulo="Exemplo no site" token="warning" /></div>
                            <TextInput id="of-instagram" v-model="form.instagram" :placeholder="emUso.instagram" />
                            <p class="mt-1 text-xs text-fg-subtle">Só o nome do perfil, ou cole o endereço dele.</p>
                            <InputError :message="form.errors.instagram" />
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-line bg-surface p-5">
                    <div class="mb-1 flex items-center gap-2">
                        <h2 class="font-display text-xl font-bold uppercase text-fg">Endereço</h2>
                        <Chip v-if="exemplos.endereco" rotulo="Exemplo no site" token="warning" />
                    </div>
                    <p class="mb-4 text-sm text-fg-muted">No site hoje: {{ emUso.endereco }}. A etiqueta "exemplo" só sai com rua, bairro, cidade, UF e CEP preenchidos.</p>
                    <div class="grid gap-4 sm:grid-cols-6">
                        <div class="sm:col-span-4">
                            <InputLabel for="of-logradouro" value="Rua e número" />
                            <TextInput id="of-logradouro" v-model="form.logradouro" maxlength="160" placeholder="Rua das Oficinas, 123" />
                            <InputError :message="form.errors.logradouro" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel for="of-bairro" value="Bairro" />
                            <TextInput id="of-bairro" v-model="form.bairro" maxlength="80" />
                            <InputError :message="form.errors.bairro" />
                        </div>
                        <div class="sm:col-span-3">
                            <InputLabel for="of-cidade" value="Cidade" />
                            <TextInput id="of-cidade" v-model="form.cidade" maxlength="80" placeholder="São Paulo" />
                            <InputError :message="form.errors.cidade" />
                        </div>
                        <div class="sm:col-span-1">
                            <InputLabel for="of-uf" value="UF" />
                            <TextInput id="of-uf" v-model="form.uf" maxlength="2" placeholder="SP" />
                            <InputError :message="form.errors.uf" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel for="of-cep" value="CEP" />
                            <TextInput id="of-cep" v-model="form.cep" inputmode="numeric" maxlength="9" placeholder="01000-000" />
                            <InputError :message="form.errors.cep" />
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-line bg-surface p-5">
                    <h2 class="mb-1 font-display text-xl font-bold uppercase text-fg">Mapa do Google</h2>
                    <p class="mb-4 text-sm text-fg-muted">
                        Com o código aqui, o site mostra o mapa na seção "Onde estamos". Sem ele, a seção fica só com o endereço e o botão "Traçar rota".
                    </p>
                    <InputLabel for="of-mapa" value="Código de incorporação" />
                    <textarea id="of-mapa" v-model="form.mapa" rows="3" maxlength="4000" :class="campoTexto" placeholder="<iframe src=&quot;https://www.google.com/maps/embed?pb=…&quot;></iframe>" />
                    <p class="mt-1 text-xs text-fg-subtle">
                        No Google Maps: procure a oficina → Compartilhar → Incorporar um mapa → Copiar HTML, e cole o código inteiro. Para tirar o mapa do site, apague o campo e salve.
                    </p>
                    <InputError :message="form.errors.mapa" />

                    <iframe v-if="oficina.mapa" :src="oficina.mapa" title="Prévia do mapa salvo" loading="lazy" class="mt-4 h-56 w-full rounded-md border border-line" />

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="of-lat" value="Latitude" />
                            <TextInput id="of-lat" v-model="form.latitude" inputmode="decimal" placeholder="-23.5505" />
                            <InputError :message="form.errors.latitude" />
                        </div>
                        <div>
                            <InputLabel for="of-lng" value="Longitude" />
                            <TextInput id="of-lng" v-model="form.longitude" inputmode="decimal" placeholder="-46.6333" />
                            <InputError :message="form.errors.longitude" />
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-fg-subtle">Opcional, é o que o Google usa para pôr a oficina no ponto certo. No Google Maps, clique com o botão direito no pino: os dois números aparecem no topo do menu.</p>
                </section>

                <section class="rounded-lg border border-line bg-surface p-5">
                    <div class="mb-1 flex items-center gap-2">
                        <h2 class="font-display text-xl font-bold uppercase text-fg">Horário</h2>
                        <Chip v-if="exemplos.horario" rotulo="Exemplo no site" token="warning" />
                    </div>
                    <label class="mb-4 flex items-start gap-3">
                        <Checkbox v-model:checked="form.horario_informado" />
                        <span class="text-sm text-fg">
                            Este é o horário de verdade da oficina
                            <span class="block text-fg-muted">Desmarcado, o site mostra o horário abaixo como exemplo, com a etiqueta.</span>
                        </span>
                    </label>

                    <div class="divide-y divide-line">
                        <div v-for="(dia, i) in form.semana" :key="dia.dia" class="flex flex-wrap items-center gap-x-4 gap-y-2 py-2.5">
                            <label class="flex w-36 items-center gap-2 text-sm font-semibold text-fg">
                                <Checkbox v-model:checked="dia.aberto" />{{ dia.nome }}
                            </label>
                            <template v-if="dia.aberto">
                                <label class="flex items-center gap-2 text-sm text-fg-muted">
                                    <span>abre</span>
                                    <input v-model="dia.abre" type="time" :class="campoHora" :aria-label="`${dia.nome}: abre às`" />
                                </label>
                                <label class="flex items-center gap-2 text-sm text-fg-muted">
                                    <span>fecha</span>
                                    <input v-model="dia.fecha" type="time" :class="campoHora" :aria-label="`${dia.nome}: fecha às`" />
                                </label>
                            </template>
                            <span v-else class="text-sm text-fg-subtle">Fechado</span>
                            <InputError class="basis-full" :message="form.errors[`semana.${i}.abre`] || form.errors[`semana.${i}.fecha`]" />
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-fg-subtle">Dias seguidos com o mesmo horário aparecem juntos no site ("Segunda a sexta"). Domingo fechado aparece como "Domingo e feriado".</p>
                </section>
            </fieldset>

            <div v-if="podeGerenciar" class="flex flex-wrap items-center gap-3">
                <PrimaryButton :disabled="form.processing">{{ form.processing ? 'Salvando…' : 'Salvar' }}</PrimaryButton>
                <a :href="route('site.home') + '#onde'" target="_blank" rel="noopener" class="text-sm text-accent hover:underline">Ver no site</a>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
