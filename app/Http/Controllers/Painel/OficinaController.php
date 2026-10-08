<?php

declare(strict_types=1);

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\Oficina;
use App\Support\MapaIncorporado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OficinaController extends Controller
{
    private const DIAS = [1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado', 0 => 'Domingo'];

    public function edit(Request $request): Response
    {
        $oficina = Oficina::atual();
        $semana = $oficina->semana();

        return Inertia::render('Painel/Oficina/Editar', [

            'oficina' => [
                ...$oficina->only(['whatsapp', 'telefone', 'email', 'instagram', 'logradouro', 'bairro', 'cidade', 'uf', 'cep', 'latitude', 'longitude']),
                'mapa' => $oficina->mapa_embed,
            ],
            'horarioInformado' => $oficina->horario !== null,
            'semana' => collect(self::DIAS)->map(fn (string $nome, int $dia) => [
                'dia' => $dia,
                'nome' => $nome,
                'aberto' => $semana[$dia] !== null,
                'abre' => $semana[$dia]['abre'] ?? '08:00',
                'fecha' => $semana[$dia]['fecha'] ?? '18:00',
            ])->values(),
            'emUso' => [
                'whatsapp' => Oficina::formatar($oficina->numeroDoWhatsapp()),
                'email' => $oficina->valor('email'),
                'instagram' => '@'.$oficina->valor('instagram'),
                'endereco' => $oficina->enderecoLinha1().' · '.$oficina->enderecoLinha2(),
            ],
            'exemplos' => collect(array_keys(Oficina::BLOCOS))->mapWithKeys(fn (string $b) => [$b => $oficina->exemplo($b)]),
            'podeGerenciar' => $request->user()?->can('oficina.gerenciar') ?? false,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge([
            'whatsapp' => $this->soDigitos($request->input('whatsapp')),
            'telefone' => $this->soDigitos($request->input('telefone')),
            'instagram' => $this->arroba($request->input('instagram')),
            'uf' => filled($request->input('uf')) ? mb_strtoupper(trim((string) $request->input('uf'))) : null,
        ]);

        $dados = $request->validate([

            'whatsapp' => ['nullable', 'regex:/^55\d{10,11}$/'],
            'telefone' => ['nullable', 'regex:/^55\d{10,11}$/'],
            'email' => ['nullable', 'email', 'max:120'],
            'instagram' => ['nullable', 'regex:/^[A-Za-z0-9._]{1,30}$/'],
            'logradouro' => ['nullable', 'string', 'max:160'],
            'bairro' => ['nullable', 'string', 'max:80'],
            'cidade' => ['nullable', 'string', 'max:80'],
            'uf' => ['nullable', 'alpha', 'size:2'],
            'cep' => ['nullable', 'regex:/^\d{5}-?\d{3}$/'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'mapa' => ['nullable', 'string', 'max:4000'],
            'horario_informado' => ['boolean'],
            'semana' => ['array', 'size:7'],
            'semana.*.dia' => ['required', 'integer', 'between:0,6', 'distinct'],
            'semana.*.aberto' => ['boolean'],
            'semana.*.abre' => ['nullable', 'date_format:H:i'],
            'semana.*.fecha' => ['nullable', 'date_format:H:i'],
        ], [
            'whatsapp.regex' => 'Informe o WhatsApp com DDD, por exemplo (11) 90000-0000.',
            'telefone.regex' => 'Informe o telefone com DDD, por exemplo (11) 2909-0000.',
            'instagram.regex' => 'Informe só o nome do perfil, por exemplo amelhoroficina.',
            'cep.regex' => 'O CEP tem oito números, por exemplo 01000-000.',
        ]);

        $mapa = null;

        if (filled($dados['mapa'] ?? null)) {
            $mapa = MapaIncorporado::reconhecer($dados['mapa'])
                ?? throw ValidationException::withMessages([
                    'mapa' => 'Não reconheci um mapa do Google. No Google Maps: Compartilhar → Incorporar um mapa → Copiar HTML, e cole aqui.',
                ]);
        }

        $horario = null;

        if ($request->boolean('horario_informado')) {
            $horario = [];

            foreach ($dados['semana'] ?? [] as $i => $dia) {
                if (! ($dia['aberto'] ?? false)) {
                    $horario[(int) $dia['dia']] = null;

                    continue;
                }

                if (blank($dia['abre'] ?? null) || blank($dia['fecha'] ?? null) || $dia['fecha'] <= $dia['abre']) {
                    throw ValidationException::withMessages([
                        "semana.{$i}.abre" => 'Em dia aberto, informe a hora de abrir e a de fechar, com a de fechar depois.',
                    ]);
                }

                $horario[(int) $dia['dia']] = ['abre' => $dia['abre'], 'fecha' => $dia['fecha']];
            }
        }

        if (filled($dados['cep'] ?? null)) {
            $cep = preg_replace('/\D/', '', $dados['cep']);
            $dados['cep'] = substr($cep, 0, 5).'-'.substr($cep, 5);
        }

        $oficina = Oficina::query()->first() ?? new Oficina;
        $oficina->fill([
            ...collect($dados)->only(['whatsapp', 'telefone', 'email', 'instagram', 'logradouro', 'bairro', 'cidade', 'uf', 'cep', 'latitude', 'longitude'])
                ->map(fn ($v) => $v === '' ? null : $v)->all(),
            'mapa_embed' => $mapa,
            'horario' => $horario,
        ])->save();

        return back()->with('sucesso', 'Dados da oficina salvos. O site já mostra o que mudou.');
    }

    private function soDigitos(mixed $valor): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $valor);

        if ($digitos === '') {
            return null;
        }

        return strlen($digitos) <= 11 ? '55'.$digitos : $digitos;
    }

    private function arroba(mixed $valor): ?string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        if (preg_match('#instagram\.com/([^/?\#]+)#i', $valor, $achado) === 1) {
            $valor = $achado[1];
        }

        return ltrim($valor, '@');
    }
}
