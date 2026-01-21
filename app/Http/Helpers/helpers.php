<?php

use App\GroupPermission;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;

/***************** User Roles Permissions ****************************/
function checkRolePermission($module_page)
{
    $_SESSION["group_id"] = Auth::user()->group_id;

    $group_id = $_SESSION["group_id"];
    return  GroupPermission::where(['group_id' => $group_id])->where('module_page', $module_page)->first();
}
/***************** User Roles Permissions ****************************/


function allRoutes()
{
    // Map routes to their respective names
    $routeMap = [
        'dashboard' => 'Dashboard',
        'users' => 'Users',
        'roles' => 'Roles',
        'clients' => 'Clients',
        'case.index' => 'Case List',
        'create.case' => 'Create Case',
        'invoices' => 'Invoices',
        'courts.index' => 'Courts',
        'court_category.index' => 'Court Category',
        'quotations.index' => 'Quotations',
        'general.settings' => 'General Settings',
        'email.config' => 'SMTP Settings',
        'countries' => 'Countries',
        'states' => 'States',
        'cities' => 'City',
        'fee.description' => 'Fee Description',
        'case.acts' => 'Case Acts',
        'profile' => 'Profile - ' . Auth::guard('web')->user()->full_name,
    ];

    // Get the current route name
    $route = request()->route() ? request()->route()->getName() : null;

    // Return the mapped route name or an empty string if not found
    return $routeMap[$route] ?? '';
}

function currencyList()
{
    $currencies = [
        'AED' => 'United Arab Emirates Dirham (AED)',
        'AFN' => 'Afghan Afghani (AFN)',
        'ALL' => 'Albanian Lek (ALL)',
        'AMD' => 'Armenian Dram (AMD)',
        'ANG' => 'Netherlands Antillean Guilder (ANG)',
        'AOA' => 'Angolan Kwanza (AOA)',
        'ARS' => 'Argentine Peso (ARS)',
        'AUD' => 'Australian Dollar (AUD)',
        'AWG' => 'Aruban Florin (AWG)',
        'AZN' => 'Azerbaijani Manat (AZN)',
        'BAM' => 'Bosnia and Herzegovina Convertible Mark (BAM)',
        'BBD' => 'Barbadian Dollar (BBD)',
        'BDT' => 'Bangladeshi Taka (BDT)',
        'BGN' => 'Bulgarian Lev (BGN)',
        'BHD' => 'Bahraini Dinar (BHD)',
        'BIF' => 'Burundian Franc (BIF)',
        'BMD' => 'Bermudian Dollar (BMD)',
        'BND' => 'Brunei Dollar (BND)',
        'BOB' => 'Bolivian Boliviano (BOB)',
        'BOV' => 'Bolivian Mvdol (BOV)',
        'BRL' => 'Brazilian Real (BRL)',
        'BSD' => 'Bahamian Dollar (BSD)',
        'BTN' => 'Bhutanese Ngultrum (BTN)',
        'BWP' => 'Botswana Pula (BWP)',
        'BYN' => 'Belarusian Ruble (BYN)',
        'BZD' => 'Belize Dollar (BZD)',
        'CAD' => 'Canadian Dollar (CAD)',
        'CDF' => 'Congolese Franc (CDF)',
        'CHE' => 'WIR Euro (CHE)',
        'CHF' => 'Swiss Franc (CHF)',
        'CHW' => 'WIR Franc (CHW)',
        'CLF' => 'Chilean Unidad de Fomento (CLF)',
        'CLP' => 'Chilean Peso (CLP)',
        'CNY' => 'Chinese Yuan (CNY)',
        'COP' => 'Colombian Peso (COP)',
        'COU' => 'Unidad de Valor Real (COU)',
        'CRC' => 'Costa Rican Colon (CRC)',
        'CUC' => 'Cuban Convertible Peso (CUC)',
        'CUP' => 'Cuban Peso (CUP)',
        'CVE' => 'Cape Verdean Escudo (CVE)',
        'CZK' => 'Czech Koruna (CZK)',
        'DJF' => 'Djiboutian Franc (DJF)',
        'DKK' => 'Danish Krone (DKK)',
        'DOP' => 'Dominican Peso (DOP)',
        'DZD' => 'Algerian Dinar (DZD)',
        'EGP' => 'Egyptian Pound (EGP)',
        'ERN' => 'Eritrean Nakfa (ERN)',
        'ETB' => 'Ethiopian Birr (ETB)',
        'EUR' => 'Euro (EUR)',
        'FJD' => 'Fijian Dollar (FJD)',
        'FKP' => 'Falkland Islands Pound (FKP)',
        'GBP' => 'British Pound Sterling (GBP)',
        'GEL' => 'Georgian Lari (GEL)',
        'GHS' => 'Ghanaian Cedi (GHS)',
        'GIP' => 'Gibraltar Pound (GIP)',
        'GMD' => 'Gambian Dalasi (GMD)',
        'GNF' => 'Guinean Franc (GNF)',
        'GTQ' => 'Guatemalan Quetzal (GTQ)',
        'GYD' => 'Guyanese Dollar (GYD)',
        'HKD' => 'Hong Kong Dollar (HKD)',
        'HNL' => 'Honduran Lempira (HNL)',
        'HRK' => 'Croatian Kuna (HRK)',
        'HTG' => 'Haitian Gourde (HTG)',
        'HUF' => 'Hungarian Forint (HUF)',
        'IDR' => 'Indonesian Rupiah (IDR)',
        'ILS' => 'Israeli New Shekel (ILS)',
        'INR' => 'Indian Rupee (INR)',
        'IQD' => 'Iraqi Dinar (IQD)',
        'IRR' => 'Iranian Rial (IRR)',
        'ISK' => 'Icelandic Krona (ISK)',
        'JMD' => 'Jamaican Dollar (JMD)',
        'JOD' => 'Jordanian Dinar (JOD)',
        'JPY' => 'Japanese Yen (JPY)',
        'KES' => 'Kenyan Shilling (KES)',
        'KGS' => 'Kyrgyzstani Som (KGS)',
        'KHR' => 'Cambodian Riel (KHR)',
        'KMF' => 'Comorian Franc (KMF)',
        'KPW' => 'North Korean Won (KPW)',
        'KRW' => 'South Korean Won (KRW)',
        'KWD' => 'Kuwaiti Dinar (KWD)',
        'KYD' => 'Cayman Islands Dollar (KYD)',
        'KZT' => 'Kazakhstani Tenge (KZT)',
        'LAK' => 'Lao Kip (LAK)',
        'LBP' => 'Lebanese Pound (LBP)',
        'LKR' => 'Sri Lankan Rupee (LKR)',
        'LRD' => 'Liberian Dollar (LRD)',
        'LSL' => 'Lesotho Loti (LSL)',
        'LYD' => 'Libyan Dinar (LYD)',
        'MAD' => 'Moroccan Dirham (MAD)',
        'MDL' => 'Moldovan Leu (MDL)',
        'MGA' => 'Malagasy Ariary (MGA)',
        'MKD' => 'Macedonian Denar (MKD)',
        'MMK' => 'Myanmar Kyat (MMK)',
        'MNT' => 'Mongolian Tugrik (MNT)',
        'MOP' => 'Macanese Pataca (MOP)',
        'MRU' => 'Mauritanian Ouguiya (MRU)',
        'MUR' => 'Mauritian Rupee (MUR)',
        'MVR' => 'Maldivian Rufiyaa (MVR)',
        'MWK' => 'Malawian Kwacha (MWK)',
        'MXN' => 'Mexican Peso (MXN)',
        'MXV' => 'Mexican Unidad de Inversión (MXV)',
        'MYR' => 'Malaysian Ringgit (MYR)',
        'MZN' => 'Mozambican Metical (MZN)',
        'NAD' => 'Namibian Dollar (NAD)',
        'NGN' => 'Nigerian Naira (NGN)',
        'NIO' => 'Nicaraguan Córdoba (NIO)',
        'NOK' => 'Norwegian Krone (NOK)',
        'NPR' => 'Nepalese Rupee (NPR)',
        'NZD' => 'New Zealand Dollar (NZD)',
        'OMR' => 'Omani Rial (OMR)',
        'PAB' => 'Panamanian Balboa (PAB)',
        'PEN' => 'Peruvian Sol (PEN)',
        'PGK' => 'Papua New Guinean Kina (PGK)',
        'PHP' => 'Philippine Peso (PHP)',
        'PKR' => 'Pakistani Rupee (PKR)',
        'PLN' => 'Polish Złoty (PLN)',
        'PYG' => 'Paraguayan Guaraní (PYG)',
        'QAR' => 'Qatari Riyal (QAR)',
        'RON' => 'Romanian Leu (RON)',
        'RSD' => 'Serbian Dinar (RSD)',
        'RUB' => 'Russian Ruble (RUB)',
        'RWF' => 'Rwandan Franc (RWF)',
        'SAR' => 'Saudi Riyal (SAR)',
        'SBD' => 'Solomon Islands Dollar (SBD)',
        'SCR' => 'Seychellois Rupee (SCR)',
        'SDG' => 'Sudanese Pound (SDG)',
        'SEK' => 'Swedish Krona (SEK)',
        'SGD' => 'Singapore Dollar (SGD)',
        'SHP' => 'Saint Helena Pound (SHP)',
        'SLL' => 'Sierra Leonean Leone (SLL)',
        'SOS' => 'Somali Shilling (SOS)',
        'SRD' => 'Surinamese Dollar (SRD)',
        'SSP' => 'South Sudanese Pound (SSP)',
        'STN' => 'São Tomé and Príncipe Dobra (STN)',
        'SVC' => 'Salvadoran Colón (SVC)',
        'SYP' => 'Syrian Pound (SYP)',
        'SZL' => 'Eswatini Lilangeni (SZL)',
        'THB' => 'Thai Baht (THB)',
        'TJS' => 'Tajikistani Somoni (TJS)',
        'TMT' => 'Turkmenistani Manat (TMT)',
        'TND' => 'Tunisian Dinar (TND)',
        'TOP' => 'Tongan Paʻanga (TOP)',
        'TRY' => 'Turkish Lira (TRY)',
        'TTD' => 'Trinidad and Tobago Dollar (TTD)',
        'TWD' => 'New Taiwan Dollar (TWD)',
        'TZS' => 'Tanzanian Shilling (TZS)',
        'UAH' => 'Ukrainian Hryvnia (UAH)',
        'UGX' => 'Ugandan Shilling (UGX)',
        'USD' => 'United States Dollar (USD)',
        'USN' => 'United States Dollar (Next day) (USN)',
        'UYI' => 'Uruguay Peso en Unidades Indexadas (UYI)',
        'UYU' => 'Uruguayan Peso (UYU)',
        'UYW' => 'Unidad Previsional (UYW)',
        'UZS' => 'Uzbekistan Som (UZS)',
        'VED' => 'Venezuelan Bolívar Digital (VED)',
        'VES' => 'Venezuelan Bolívar Soberano (VES)',
        'VND' => 'Vietnamese Đồng (VND)',
        'VUV' => 'Vanuatu Vatu (VUV)',
        'WST' => 'Samoan Tālā (WST)',
        'XAF' => 'Central African CFA Franc (XAF)',
        'XAG' => 'Silver (troy ounce) (XAG)',
        'XAU' => 'Gold (troy ounce) (XAU)',
        'XBA' => 'European Composite Unit (XBA)',
        'XBB' => 'European Monetary Unit (XBB)',
        'XBC' => 'European Unit of Account 9 (XBC)',
        'XBD' => 'European Unit of Account 17 (XBD)',
        'XCD' => 'East Caribbean Dollar (XCD)',
        'XDR' => 'Special Drawing Rights (IMF) (XDR)',
        'XOF' => 'West African CFA Franc (XOF)',
        'XPD' => 'Palladium (troy ounce) (XPD)',
        'XPF' => 'CFP Franc (XPF)',
        'XPT' => 'Platinum (troy ounce) (XPT)',
        'XSU' => 'SUCRE (XSU)',
        'XTS' => 'Testing Code (XTS)',
        'XUA' => 'ADB Unit of Account (XUA)',
        'XXX' => 'No Currency (XXX)',
        'YER' => 'Yemeni Rial (YER)',
        'ZAR' => 'South African Rand (ZAR)',
        'ZMW' => 'Zambian Kwacha (ZMW)',
        'ZWL' => 'Zimbabwean Dollar (ZWL)',
    ];

    return $currencies;
}

function getDisplaySoldQuantity($quantity, $unitInfo, $unitId)
{
    if (empty($unitInfo)) {
        return $quantity . ' units';
    }

    // Find the target unit (the unit being sold)
    $targetUnit = null;
    foreach ($unitInfo as $unit) {
        if ($unit['unit_id'] == $unitId) {
            $targetUnit = $unit;
            break;
        }
    }

    if (!$targetUnit) {
        return $quantity . ' units';
    }

    $targetConversion = $targetUnit['conversion'] ?? 1;
    $quantityInBaseUnits = $quantity * $targetConversion;

    // Sort all units by conversion factor (descending)
    usort($unitInfo, function ($a, $b) {
        return $b['conversion'] <=> $a['conversion'];
    });

    $remaining = $quantityInBaseUnits;
    $output = [];

    foreach ($unitInfo as $unit) {
        if ($unit['conversion'] <= 0) continue;

        $count = floor($remaining / $unit['conversion']);
        if ($count > 0) {
            $output[] = $count . ' ' . $unit['unit'];
            $remaining = $remaining % $unit['conversion'];
        }
    }

    // Add remaining in smallest unit if any left
    if ($remaining > 0 || empty($output)) {
        $smallestUnit = end($unitInfo);
        $output[] = $remaining . ' ' . $smallestUnit['unit'];
    }

    return implode(', ', $output);
}
