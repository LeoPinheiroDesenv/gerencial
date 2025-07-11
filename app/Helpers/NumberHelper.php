<?php

if (!function_exists('converteDo0a99')) {
    /**
     * Converte um número de 0 a 99 para extenso (ex.: 33 => "trinta e três").
     */
    function converteDo0a99($num)
    {
        // Arrays para a lógica de escrita
        $d = ['', 'dez', 'vinte', 'trinta', 'quarenta', 'cinquenta',
              'sessenta', 'setenta', 'oitenta', 'noventa'];
        $d10 = ['dez', 'onze', 'doze', 'treze', 'quatorze',
                'quinze', 'dezesseis', 'dezessete', 'dezoito', 'dezenove'];
        $u = ['', 'um', 'dois', 'três', 'quatro', 'cinco',
              'seis', 'sete', 'oito', 'nove'];

        // Se for zero, retorna "zero"
        if ($num == 0) {
            return 'zero';
        }

        // Se estiver entre 1 e 9
        if ($num < 10) {
            return $u[$num];
        }

        // Se estiver entre 10 e 19
        if ($num < 20) {
            return $d10[$num - 10];
        }

        // Se for 20 ou mais
        $dezena = floor($num / 10);  // ex.: 33 -> 3 (trinta)
        $unidade = $num % 10;        // ex.: 33 -> 3

        $texto = $d[$dezena];        // ex.: "trinta"
        if ($unidade > 0) {
            $texto .= ' e ' . $u[$unidade]; // ex.: "trinta e três"
        }
        return $texto;
    }
}

if (!function_exists('valorPorExtenso')) {
    function valorPorExtenso($valor)
    {
        $singular = ['centavo', 'real', 'mil', 'milhão', 'bilhão', 'trilhão', 'quatrilhão'];
        $plural   = ['centavos', 'reais', 'mil', 'milhões', 'bilhões', 'trilhões', 'quatrilhões'];

        $c = ['', 'cem', 'duzentos', 'trezentos', 'quatrocentos',
              'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos'];
        $d = ['', 'dez', 'vinte', 'trinta', 'quarenta', 'cinquenta',
              'sessenta', 'setenta', 'oitenta', 'noventa'];
        $d10 = ['dez', 'onze', 'doze', 'treze', 'quatorze', 'quinze',
                'dezesseis', 'dezessete', 'dezoito', 'dezenove'];
        $u = ['', 'um', 'dois', 'três', 'quatro', 'cinco',
              'seis', 'sete', 'oito', 'nove'];

        // Se o valor for negativo, chama a função novamente com valor positivo e adiciona "menos"
        if ($valor < 0) {
            return 'menos ' . valorPorExtenso(-$valor);
        }

        // Parte inteira e fracionária
        $integer = floor($valor);
        $fraction = round(($valor - $integer) * 100);

        $result = [];

        // Converte a parte inteira
        if ($integer == 0) {
            $result[] = 'zero';
        } else {
            $pot = 0;
            while ($integer > 0) {
                // Pega grupos de 3 dígitos (ex.: 1.234 -> pega 234, depois 1)
                $n = $integer % 1000;
                if ($n != 0) {
                    $str = '';
                    $cen = floor($n / 100); // centenas
                    $resto = $n % 100;      // dezenas e unidades

                    // Trata centenas
                    if ($cen > 0) {
                        // Ex.: se for 100 cravado, "cem"
                        if ($cen == 1 && $resto == 0) {
                            $str .= 'cem';
                        } else {
                            $str .= $c[$cen]; // ex.: "duzentos"
                        }
                    }
                    // Trata dezenas/unidades
                    if ($resto > 0) {
                        if ($cen > 0) {
                            $str .= ' e ';
                        }
                        if ($resto < 10) {
                            $str .= $u[$resto]; // ex.: "um"
                        } elseif ($resto < 20) {
                            $str .= $d10[$resto - 10]; // ex.: "doze"
                        } else {
                            $dezena = floor($resto / 10); // ex.: 33 -> 3
                            $unidade = $resto % 10;       // ex.: 33 -> 3
                            $str .= $d[$dezena];          // ex.: "trinta"
                            if ($unidade > 0) {
                                $str .= ' e ' . $u[$unidade]; // ex.: " e três"
                            }
                        }
                    }

                    // Ajusta o nome do grupo (real/reais, mil, milhão, etc.)
                    if ($pot == 0) {
                        // Grupo das centenas: real/reais
                        $str .= ' ' . ($n == 1 ? $singular[1] : $plural[1]);
                    } else {
                        // pot >= 1 => mil, milhão, bilhão, etc.
                        $str .= ' ' . ($n == 1 ? $singular[$pot + 1] : $plural[$pot + 1]);
                    }

                    // Insere este trecho no início do array
                    array_unshift($result, $str);
                }
                $integer = floor($integer / 1000);
                $pot++;
            }
        }

        // Junta tudo com " e "
        $final = implode(' e ', $result);

        // Trata os centavos
        if ($fraction > 0) {
            // Converte o valor (0..99) para texto
            $centavosExtenso = converteDo0a99($fraction);
            // Ex.: " e trinta e três centavos"
            $final .= ' e ' . $centavosExtenso . ' ' . ($fraction == 1 ? 'centavo' : 'centavos');
        }

        return $final;
    }
}
