<?php
/**
 * Lista centralizada de bancos com logos oficiais via URLs
 * Usado em add_card.php e edit_card.php
 */

$bankList = array(
    // Redes Internacionais
    'visa' => array(
        'name' => 'Visa',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><text x="10" y="42" font-family="Arial, Helvetica, sans-serif" font-size="32" font-weight="700" fill="#1A1F71">VISA</text></svg>',
        'icon' => 'fa-cc-visa',
        'color' => '#1434CB'
    ),
    'mastercard' => array(
        'name' => 'Mastercard',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><circle cx="70" cy="30" r="22" fill="#EB001B"/><circle cx="110" cy="30" r="22" fill="#FF5F00"/></svg>',
        'icon' => 'fa-cc-mastercard',
        'color' => '#FF5F00'
    ),
    'amex' => array(
        'name' => 'American Express',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect x="20" y="8" width="160" height="44" fill="white" rx="2"/><text x="100" y="30" font-family="Arial" font-size="10" font-weight="700" fill="#006FCF" text-anchor="middle">AMERICAN</text><text x="100" y="42" font-family="Arial" font-size="10" font-weight="700" fill="#006FCF" text-anchor="middle">EXPRESS</text></svg>',
        'icon' => 'fa-cc-amex',
        'color' => '#006FCF'
    ),
    'discover' => array(
        'name' => 'Discover',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><text x="20" y="40" font-family="Arial" font-size="24" font-weight="700" fill="#FF6000">DISCOVER</text></svg>',
        'icon' => 'fa-cc-discover',
        'color' => '#FF6000'
    ),
    
    // Bancos Portugueses
    'caixa' => array(
        'name' => 'Caixa Geral',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiNDNDFFM0EiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+Q0dEPC90ZXh0Pjwvc3ZnPg==',
        'icon' => 'fa-building',
        'color' => '#C41E3A'
    ),
    'millenniumbcp' => array(
        'name' => 'Millennium BCP',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiMwMDMzNjYiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+TTwvdGV4dD48L3N2Zz4=',
        'icon' => 'fa-building',
        'color' => '#003DA5'
    ),
    'santander' => array(
        'name' => 'Santander',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiNFQzFDMjQiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+U0FOVEFOREVSKY90ZXh0Pjwvc3ZnPg==',
        'icon' => 'fa-building',
        'color' => '#EC1C24'
    ),
    'bpi' => array(
        'name' => 'BPI',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiMwMDY2QjIiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+QlBJPC90ZXh0Pjwvc3ZnPg==',
        'icon' => 'fa-building',
        'color' => '#0066B2'
    ),
    'novo_banco' => array(
        'name' => 'Novo Banco',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiNGRkQ3MDAiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSIjMzMzIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5OQjwvdGV4dD48L3N2Zz4=',
        'icon' => 'fa-building',
        'color' => '#FFD700'
    ),
    'ctt_banco' => array(
        'name' => 'CTT Banco',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiNGRkNDMDAiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSIjMzMzIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5DVFQgPC90ZXh0Pjwvc3ZnPg==',
        'icon' => 'fa-building',
        'color' => '#FFCC00'
    ),
    
    // Bancos Europeus Populares
    'deutsche' => array(
        'name' => 'Deutsche Bank',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiMwMDY2Q0MiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+REI8L3RleHQ+PC9zdmc+',
        'icon' => 'fa-building',
        'color' => '#0066CC'
    ),
    'ing' => array(
        'name' => 'ING',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiNGRjYwMDAiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+SU5HPC90ZXh0Pjwvc3ZnPg==',
        'icon' => 'fa-building',
        'color' => '#FF6000'
    ),
    'rabobank' => array(
        'name' => 'Rabobank',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiNGRkNDMDAiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSIjMzMzIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5SQUJPQkFOSzwvdGV4dD48L3N2Zz4=',
        'icon' => 'fa-building',
        'color' => '#FFCC00'
    ),
    'abn_amro' => array(
        'name' => 'ABN AMRO',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiMwMDM0NzgiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+QUJOIEFNUk88L3RleHQ+PC9zdmc+',
        'icon' => 'fa-building',
        'color' => '#003478'
    ),
    
    // Wallets/Fintech
    'paypal' => array(
        'name' => 'PayPal',
        'logo' => 'https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg',
        'icon' => 'fa-paypal',
        'color' => '#003087'
    ),
    'revolut' => array(
        'name' => 'Revolut',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiMwMDc1RUIiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+UkVWT0xVVDwvdGV4dD48L3N2Zz4=',
        'icon' => 'fa-wallet',
        'color' => '#0066FF'
    ),
    'wise' => array(
        'name' => 'Wise',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiMwMEE0RUYiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+V0lTRTwvdGV4dD48L3N2Zz4=',
        'icon' => 'fa-wallet',
        'color' => '#00A4EF'
    ),
    'n26' => array(
        'name' => 'N26',
        'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxyZWN0IHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIGZpbGw9IiMzNkExOEIiLz48dGV4dCB4PSIxMjAiIHk9IjQyIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+TjI2PC90ZXh0Pjwvc3ZnPg==',
        'icon' => 'fa-wallet',
        'color' => '#233048'
    ),
    'monese' => array(
        'name' => 'Monese',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Arial, Helvetica, sans-serif" font-size="20" font-weight="700" fill="#6C4DF5" text-anchor="middle">MONese</text></svg>',
        'icon' => 'fa-wallet',
        'color' => '#6C4DF5'
    ),
    'bunq' => array(
        'name' => 'bunq',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Verdana, Geneva, sans-serif" font-size="20" font-weight="700" fill="#00A6A6" text-anchor="middle">bunq</text></svg>',
        'icon' => 'fa-wallet',
        'color' => '#00A6A6'
    ),
    'curve' => array(
        'name' => 'Curve',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Arial" font-size="20" font-weight="700" fill="#FF2D55" text-anchor="middle">Curve</text></svg>',
        'icon' => 'fa-credit-card',
        'color' => '#FF2D55'
    ),
    'moey' => array(
        'name' => 'Moey!',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Arial" font-size="20" font-weight="700" fill="#FF8C00" text-anchor="middle">Moey!</text></svg>',
        'icon' => 'fa-wallet',
        'color' => '#FF8C00'
    ),
    'credito_agricola' => array(
        'name' => 'Crédito Agrícola',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Georgia, serif" font-size="18" font-weight="700" fill="#2B7A2B" text-anchor="middle">Crédito Agrícola</text></svg>',
        'icon' => 'fa-university',
        'color' => '#2B7A2B'
    ),
    'montepio' => array(
        'name' => 'Montepio',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Arial" font-size="20" font-weight="700" fill="#D61F26" text-anchor="middle">Montepio</text></svg>',
        'icon' => 'fa-university',
        'color' => '#D61F26'
    ),
    'activobank' => array(
        'name' => 'ActivoBank',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Verdana" font-size="18" font-weight="700" fill="#00A99D" text-anchor="middle">ActivoBank</text></svg>',
        'icon' => 'fa-university',
        'color' => '#00A99D'
    ),
    'bankinter' => array(
        'name' => 'Bankinter',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Arial" font-size="20" font-weight="700" fill="#FF8200" text-anchor="middle">Bankinter</text></svg>',
        'icon' => 'fa-university',
        'color' => '#FF8200'
    ),
    'abanca' => array(
        'name' => 'Abanca',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Arial" font-size="20" font-weight="700" fill="#0055A4" text-anchor="middle">Abanca</text></svg>',
        'icon' => 'fa-university',
        'color' => '#0055A4'
    ),
    'eurobic' => array(
        'name' => 'EuroBic',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Arial" font-size="20" font-weight="700" fill="#0033A0" text-anchor="middle">EuroBic</text></svg>',
        'icon' => 'fa-university',
        'color' => '#0033A0'
    ),
    'banco_best' => array(
        'name' => 'Banco Best',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Arial" font-size="20" font-weight="700" fill="#00AEEF" text-anchor="middle">Banco Best</text></svg>',
        'icon' => 'fa-university',
        'color' => '#00AEEF'
    ),
    'bisonbank' => array(
        'name' => 'Bison Bank',
        'logo' => null,
        'logo_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60"><rect fill="none" width="200" height="60"/><text x="100" y="38" font-family="Georgia, serif" font-size="18" font-weight="700" fill="#6B3E26" text-anchor="middle">Bison Bank</text></svg>',
        'icon' => 'fa-university',
        'color' => '#6B3E26'
    ),
    
    // Customizado
    'custom' => array(
        'name' => 'Outro',
        'logo' => null,
        'icon' => 'fa-credit-card',
        'color' => '#95A5A6'
    )
);

// Se um SVG inline (logo_svg) estiver definido, preferimos usar esse SVG como data-uri
// Isso garante que o logo no preview tenha fundo transparente quando o SVG for transparente
foreach ($bankList as $key => $b) {
    if (!empty($b['logo_svg'])) {
        // encode SVG for use in data URI (utf8 + rawurlencode to preserve characters)
        $svg = $b['logo_svg'];
        $bankList[$key]['logo'] = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
        // Determinar se o SVG tem um rect com fill diferente de 'none' (fundo opaco)
        // Se tivermos um fundo opaco, preferimos mostrar o nome do banco em vez do logo
        $useName = true;
        if (preg_match('/<rect[^>]*>/i', $svg)) {
            // se existir rect, verificar se o fill é 'none'
            if (preg_match('/<rect[^>]*fill=["\']?none["\']?/i', $svg)) {
                $useName = false;
            } else {
                $useName = true;
            }
        } else {
            // sem rect — assume transparente
            $useName = false;
        }
        $bankList[$key]['use_name'] = $useName ? true : false;
    }
}

/**
 * Agrupa bancos por categoria para exibição
 */
function getBanksByCategory() {
    global $bankList;
    
    return array(
        'Redes Internacionais' => array_slice($bankList, 0, 4),
        'Bancos Portugueses' => array_slice($bankList, 4, 6),
        'Bancos Europeus' => array_slice($bankList, 10, 4),
        'Wallets/Fintech' => array_slice($bankList, 14, 4),
        'Customizado' => array_slice($bankList, 18, 1)
    );
}

/**
 * Retorna um banco específico
 */
function getBank($key) {
    global $bankList;
    return isset($bankList[$key]) ? $bankList[$key] : $bankList['custom'];
}
