<?php
$dados = filter_input_array(INPUT_POST);
$emptyFields = false;
$vlr_inicial = 0;
$vlr_recorrente = 0;
$tx_juros = 0;
$prazo = 0;
$periodo = 'mensal';
$tempo = 'meses';

$taxa_mensal =  0;
$taxa_anual =  0;
$rendimentos = 0;
$acumulado = 0;
$renda_mensal = 0;
$renda_anual = 0;

if (isset($dados)) {

    if (!empty($dados['vlr_inicial']) || !empty($dados['vlr_recorrente'])) {
        if (empty($dados['tx_juros']) || empty($dados['prazo'])) {
            $_SESSION['msg'] = "<div class='alert alert-danger shadow' role='alert'>Taxa de Juros e Prazo devem estar preenchidos.</div>";
            $emptyFields = true;
        }
    } else {
        $_SESSION['msg'] = "<div class='alert alert-danger shadow' role='alert'>Valor Inicial e/ou Valor Recorrente devem estar preenchidos.</div>";
        $emptyFields = true;
    }

    $vlr_inicial = floatval(str_replace(',', '.', str_replace('.', '', $dados['vlr_inicial'])));
    $vlr_recorrente = floatval(str_replace(',', '.', str_replace('.', '', $dados['vlr_recorrente'])));
    $tx_juros = floatval(str_replace(',', '.', str_replace('.', '', $dados['tx_juros'])));
    $prazo = intval($dados['prazo']);
    $periodo = $dados['periodo'];

    if (!$emptyFields) {

        if ($periodo === 'mensal') {
            $taxa_mensal = $tx_juros;
            $taxa_anual = (pow(1 + $taxa_mensal / 100, 12) - 1) * 100;
        } else {
            $taxa_anual = $tx_juros;
            $taxa_mensal = (pow(1 + $taxa_anual / 100, 1 / 12) - 1) * 100;
        }

        $rendimentos = $vlr_inicial * pow(1 + $tx_juros / 100, $prazo) +
            $vlr_recorrente * ((pow(1 + $tx_juros / 100, $prazo) - 1) / ($tx_juros / 100)) - ($vlr_inicial + $vlr_recorrente * $prazo);

        $acumulado = $vlr_inicial + $prazo * $vlr_recorrente + $rendimentos;
        $renda_mensal = $acumulado * ($taxa_mensal / 100);
        $renda_anual = $acumulado * ($taxa_anual / 100);

        $tempo = $periodo === 'mensal' ? ($prazo !== 1 ? 'meses' : 'mês') : ($prazo !== 1 ? 'anos' : 'ano');
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Calculadora Financeira</title>
</head>

<body>
    <div class="container text-disabled mt-3" style="font-size: 14px;">
        <div class="row">
            <div class="col-sm shadow">
                <h3>Calculadora Financeira</h3>
                <p>Os campos com (*) são obrigatórios.</p>

                <?php
                if (isset($_SESSION['msg'])) {
                    echo $_SESSION['msg'];
                    unset($_SESSION['msg']);
                }
                ?>

                <form method="post">
                    <div class="row mt-3">
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="vlr_inicial" class="form-label">Valor Inicial (* e/ou Valor Recorrente): </label>
                                <input type="text" name="vlr_inicial" id="vlr_inicial" class="form-control vlr" value="<?= $vlr_inicial > 0 ? number_format($vlr_inicial, 2, ',', '.') : ''; ?>" placeholder="Ex.: 1.000,00">
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="vlr_recorrente" class="form-label">Valor Recorrente (* e/ou Valor Inicial): </label>
                                <input type="text" name="vlr_recorrente" id="vlr_recorrente" class="form-control vlr" value="<?= $vlr_recorrente > 0 ? number_format($vlr_recorrente, 2, ',', '.') : ''; ?>" placeholder="Ex.: 1.000,00">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="tx_juros" class="form-label">Taxa de Juros (4 decimais) (*): </label>
                                <input type="text" name="tx_juros" id="tx_juros" class="form-control juros" value="<?= $tx_juros > 0 ?  number_format($tx_juros, 4, ',', '.') : ''; ?>" placeholder="Ex.: 0,9825">
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="prazo" class="form-label">Prazo (*): </label>
                                <input type="text" name="prazo" id="prazo" class="form-control" value="<?= $prazo ?: '' ?>" placeholder="Ex.: 60">
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="periodo" class="form-label">Período: </label>
                                <select name="periodo" id="periodo" class="form-control">
                                    <option <?= $periodo == 'mensal' ? 'selected' : ''; ?> value="mensal">Mensal</option>
                                    <option <?= $periodo == 'anual' ? 'selected' : ''; ?> value="anual">Anual</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-sm d-flex justify-content-between">
                            <input type="reset" value="Limpar" class="btn btn-warning shadow" onclick=" window.location.replace('index.php')">
                            <input type="submit" value="Calcular" class="btn btn-success shadow">
                        </div>
                    </div>
                </form>

                <hr>

                <div>
                    <h4 class="mb-3">Resultados: </h4>

                    <p><strong>Prazo: </strong> <span class="result-prazo"><?= $prazo; ?> </span> <span class="result-periodo"><?= $tempo; ?></span></p>

                    <p><strong>Valor Investido Total: </strong> <?= number_format($vlr_inicial + $prazo * $vlr_recorrente, 2, ',', '.'); ?>
                    <ul>
                        <li>
                            <p><strong>Valor Inicial: </strong> <span class="result-vlr-inicial"><?= number_format($vlr_inicial, 2, ',', '.'); ?> </span></p>
                        </li>
                        <li>
                            <p><strong>Valor Recorrente: </strong>
                                <span class="result-vlr-recorrente-total"><?= number_format($prazo * $vlr_recorrente, 2, ',', '.'); ?></span>
                                (<span class="result-prazo"><?= $prazo; ?> </span> x <span class="result-vlr-recorrente"><?= number_format($vlr_recorrente, 2, ',', '.'); ?></span>)
                            </p>
                        </li>
                    </ul>
                    </p>

                    <p><strong>Taxa Mensal: </strong> <span class="result-tx-mensal"><?= number_format($taxa_mensal, 4, ',', '.'); ?> </span>%</p>

                    <p><strong>Taxa Anual: </strong> <span class="result-tx-anual"><?= number_format($taxa_anual, 4, ',', '.'); ?> </span>%</p>

                    <p><strong>Total Rendimentos: </strong> <span class="result-vlr-rendimentos"> </span> <?= number_format($rendimentos, 2, ',', '.'); ?> </span></p>

                    <p><strong>Valor Acumulado: </strong> <span class="result-vlr-acumulado"><?= number_format($acumulado, 2, ',', '.'); ?> </span></p>

                    <p><strong>Rendimento Mensal Vitalício (Renda Passiva Mensal): </strong> <span class="result-renda-mensal"><?= number_format($renda_mensal, 2, ',', '.'); ?> </span></p>

                    <p><strong>Rendimento Anual Vitalício (Renda Passiva Anual): </strong> <span class="result-renda-anual"><?= number_format($renda_anual, 2, ',', '.'); ?> </span></p>

                </div>
            </div>

            <div class="col-sm mt-3">
                <div>
                    <h4>Como usar a Calculadora Financeira</h4>
                    <ul>
                        <li>Digite o Valor Inicial ou o Valor Recorrente, ou ambos. Pelo menos um desses campos deve ser preenchido.</li>
                        <li>Digite a Taxa de Juros mensal ou anual, com 4 dígitos nas casas decimais. Essa taxa pode ser estimada ou escolhida de uma aplicação financeira.</li>
                        <li>Digite o Prazo em meses ou anos. Apenas o valor.</li>
                        <li>Selecione o Período da Taxa de Juros e do Prazo digitados nos campos anteriores, mensal ou anual.</li>
                        <li>Caso queira digitar os dados novamentes, clique em Limpar, para, como o nome já diz, limpar todos os campos e começar novamente.</li>
                        <li>Com todos os campos preenchidos, clique em Calcular. Os resultados serão apresentados abaixo da Calculadora Financeira.</li>
                    </ul>
                </div>

                <hr>

                <div>
                    <h4 class="mb-4">A Calculadora Financeira trabalha com Juros Compostos.</h4>
                    <h5>Mas o que são juros compostos?</h5>
                    <p>Juros compostos são uma forma de cálculo de juros onde os juros acumulados ao longo do tempo são adicionados ao capital inicial, e os juros futuros são calculados sobre o novo montante (capital inicial + juros acumulados). Isso significa que os juros são "compostos" ao longo do tempo, resultando em um crescimento exponencial do valor total.</p>

                    <h5 class="mt-4">Fórmula dos Juros Compostos</h5>
                    <p>A fórmula geral para calcular o montante (M) com juros compostos é:</p>
                    <pre><code>M = P × (1 + r/n)<sup>n × t</sup></code></pre>
                    <ul>
                        <li><strong>M</strong> é o montante final.</li>
                        <li><strong>P</strong> é o capital inicial (principal).</li>
                        <li><strong>r</strong> é a taxa de juros nominal (expressa como decimal).</li>
                        <li><strong>n</strong> é o número de vezes que os juros são compostos por período.</li>
                        <li><strong>t</strong> é o número de períodos de tempo.</li>
                    </ul>

                    <h5 class="mt-4">Exemplos de Juros Compostos</h5>

                    <h6>Exemplo 1: Juros Compostos Anuais</h6>
                    <p>Se você investir R$ 1.000,00 em uma conta de poupança com uma taxa de juros anual de 5% por 3 anos, os juros compostos podem ser calculados da seguinte forma:</p>
                    <ul>
                        <li>Capital inicial (P): R$ 1.000,00</li>
                        <li>Taxa de juros anual (r): 5% ou 0,05</li>
                        <li>Número de períodos (t): 3 anos</li>
                        <li>Número de vezes que os juros são compostos por ano (n): 1</li>
                    </ul>
                    <pre><code>M = 1000 × (1 + 0.05/1)<sup>1 × 3</sup>
M = 1000 × (1 + 0.05)<sup>3</sup>
M = 1000 × (1.05)<sup>3</sup>
M = 1000 × 1.157625
M = 1157.63</code></pre>
                    <p>O montante após 3 anos será R$ 1.157,63.</p>

                    <h6>Exemplo 2: Juros Compostos Mensais</h6>
                    <p>Se você investir R$ 1.000,00 em uma conta de poupança com uma taxa de juros anual de 5%, mas os juros são compostos mensalmente, por 3 anos, o cálculo seria:</p>
                    <ul>
                        <li>Capital inicial (P): R$ 1.000,00</li>
                        <li>Taxa de juros anual (r): 5% ou 0,05</li>
                        <li>Número de períodos (t): 3 anos</li>
                        <li>Número de vezes que os juros são compostos por ano (n): 12</li>
                    </ul>
                    <pre><code>M = 1000 × (1 + 0.05/12)<sup>12 × 3</sup>
M = 1000 × (1 + 0.0041667)<sup>36</sup>
M = 1000 × (1.0041667)<sup>36</sup>
M = 1000 × 1.161617
M = 1161.62</code></pre>
                    <p>O montante após 3 anos, com composição mensal, será R$ 1.161,62.</p>

                    <h5 class="mt-4">Importância dos Juros Compostos</h5>
                    <p>Os juros compostos são amplamente utilizados em finanças e investimentos devido ao seu efeito de acumulação, que permite que os investimentos cresçam mais rapidamente do que os juros simples. Isso torna os juros compostos uma ferramenta poderosa para investidores e poupadores que desejam maximizar seus retornos ao longo do tempo.</p>

                    <h5 class="mt-4">Conclusão</h5>
                    <p>Os juros compostos são uma maneira eficiente de aumentar o valor do investimento ao longo do tempo devido ao efeito de acumulação de juros sobre juros. Entender essa fórmula e como aplicá-la pode ajudar a tomar decisões financeiras mais informadas e otimizar o crescimento do capital.</p>
                </div>
            </div>
        </div>
    </div>
    </div>

    <br>
    <br>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="jquery.mask.min.js"></script>
    <script>
        $('.vlr').mask("#.##0,00", {
            reverse: true
        });
        $('.juros').mask("000,0000", {
            reverse: true
        });
    </script>
</body>

</html>