<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Precisa de ajuda com o seu carrinho?</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f2eb; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #2d241e;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f5f2eb; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #e8e1d5;">
                    <tr>
                        <td style="background-color: #421522; padding: 25px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: 0.5px;">
                                📚 Biblioteca
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 35px 30px 25px 30px;">
                            <h2 style="margin: 0 0 15px 0; color: #2d241e; font-size: 20px; font-weight: 700;">
                                Olá, {{ $user->name }}!
                            </h2>
                            <p style="margin: 0 0 15px 0; font-size: 14px; line-height: 1.6; color: #5c4d43;">
                                Notámos que guardou recentemente alguns livros no seu carrinho de compras na nossa biblioteca, mas não chegou a concluir a sua encomenda.
                            </p>
                            <p style="margin: 0 0 25px 0; font-size: 14px; line-height: 1.6; color: #5c4d43;">
                                Ficou com alguma dúvida sobre as obras selecionadas, sobre o processo de entrega ou necessita de algum esclarecimento adicional? Estamos aqui para ajudar.
                            </p>

                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin-bottom: 25px; border: 1px solid #e8e1d5; border-radius: 8px; overflow: hidden;">
                                <thead>
                                    <tr style="background-color: #f9f7f2; border-bottom: 2px solid #e8e1d5;">
                                        <th align="left" style="padding: 10px 12px; font-size: 11px; text-transform: uppercase; color: #786457; letter-spacing: 0.5px;">Livro</th>
                                        <th align="center" style="padding: 10px 12px; font-size: 11px; text-transform: uppercase; color: #786457; letter-spacing: 0.5px;">Qtd.</th>
                                        <th align="right" style="padding: 10px 12px; font-size: 11px; text-transform: uppercase; color: #786457; letter-spacing: 0.5px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($itens as $item)
                                        <tr style="border-bottom: 1px solid #f0eae1;">
                                            <td style="padding: 12px; font-size: 13px;">
                                                <strong style="color: #2d241e; display: block;">{{ $item->livro->nome }}</strong>
                                                <span style="color: #786457; font-size: 11px;">
                                                    {{ $item->livro->autores->pluck('nome')->implode(', ') }}
                                                </span>
                                            </td>
                                            <td align="center" style="padding: 12px; font-size: 13px; color: #2d241e; font-weight: 600;">
                                                {{ $item->quantidade }}
                                            </td>
                                            <td align="right" style="padding: 12px; font-size: 13px; color: #2d241e; font-weight: 600; font-family: monospace;">
                                                {{ number_format(($item->livro->preco_centimos * $item->quantidade) / 100, 2, ',', '.') }} €
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #f9f7f2;">
                                        <td colspan="2" align="right" style="padding: 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #2d241e;">
                                            Total do Carrinho:
                                        </td>
                                        <td align="right" style="padding: 12px; font-size: 15px; font-weight: 800; color: #7a283b; font-family: monospace;">
                                            {{ number_format($totalCentimos / 100, 2, ',', '.') }} €
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center" style="background-color: #7a283b; border-radius: 6px;">
                                                    <a href="{{ route('carrinho.index') }}" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 14px; color: #ffffff; text-decoration: none; font-weight: 700; letter-spacing: 0.3px;">
                                                        Retomar o Meu Carrinho
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #786457; text-align: center;">
                                Se já concluiu o seu pedido entretanto ou se não tem interesse nesta encomenda, pedimos que ignore esta mensagem.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #f9f7f2; padding: 20px 30px; text-align: center; border-top: 1px solid #e8e1d5;">
                            <p style="margin: 0; font-size: 11px; color: #8c786a;">
                                Projeto Biblioteca © 2026 — Inovcorp. Notificação automática de apoio ao leitor.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>