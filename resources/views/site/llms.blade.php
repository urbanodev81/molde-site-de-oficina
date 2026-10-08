# A melhor oficina

> Oficina mecânica especializada em carros de passeio, nacionais e importados, em {{ $oficina->valor('cidade') }} ({{ $oficina->valor('uf') }}). Orçamento gratuito pelo WhatsApp, aprovado antes de qualquer serviço; a peça trocada é devolvida ao cliente.

## Serviços

- Revisão preventiva
- Freios (pastilhas, discos, fluido, ABS)
- Suspensão e direção
- Motor (correia dentada, juntas, vazamentos, retífica)
- Injeção eletrônica (bicos, sensores, corpo de borboleta, ignição)
- Troca de óleo e filtros
- Embreagem e câmbio manual
- Arrefecimento (radiador, bomba d'água, válvula termostática)
- Diagnóstico por scanner

## Como funciona

1. O cliente chama pelo WhatsApp ou pelo formulário do site.
2. O carro passa por avaliação e, quando precisa, pelo scanner.
3. O orçamento separa peça e mão de obra; o serviço só começa com a aprovação.
4. O carro é entregue testado, com a peça velha e a garantia anotada.

## Atendimento

- Site: {{ rtrim((string) config('oficina.dominio'), '/') }}/
- WhatsApp: {{ \App\Models\Oficina::formatar($oficina->numeroDoWhatsapp()) }}
@unless ($oficina->exemplo('email'))
- E-mail: {{ $oficina->valor('email') }}
@endunless
- Endereço: {{ $oficina->enderecoLinha1() }}, {{ $oficina->valor('cidade') }}, {{ $oficina->valor('uf') }}
- Horário: {{ $oficina->horarioEmFrase() }}
@if ($oficina->exemplo('endereco') || $oficina->exemplo('horario'))

## Observação

{{ collect(['endereco' => 'Endereço', 'horario' => 'Horário'])->filter(fn ($r, $b) => $oficina->exemplo($b))->implode(' e ') }} acima ainda {{ $oficina->exemplo('endereco') && $oficina->exemplo('horario') ? 'são de EXEMPLO e não devem ser citados' : 'é de EXEMPLO e não deve ser citado' }} como dado da oficina até esta seção ser removida.
@endif
