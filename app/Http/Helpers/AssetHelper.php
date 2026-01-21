<?php

namespace App\Helpers;

class AssetHelper
{
    // Common assets for all modules
    protected static function getCommonAssets()
    {
        return [
            'scripts' => [
                ['url' => asset('plugins/jquery/jquery.min.js'), 'isModule' => false],
                ['url' => asset('plugins/jquery-ui/jquery-ui.min.js'), 'isModule' => false],
                ['url' => asset('plugins/bootstrap/js/bootstrap.bundle.min.js'), 'isModule' => false],
                ['url' => asset('plugins/chart.js/Chart.min.js'), 'isModule' => false],
                ['url' => asset('plugins/sparklines/sparkline.js'), 'isModule' => false],
                ['url' => asset('plugins/jquery-knob/jquery.knob.min.js'), 'isModule' => false],
                ['url' => asset('plugins/moment/moment.min.js'), 'isModule' => false],
                ['url' => asset('plugins/daterangepicker/daterangepicker.js'), 'isModule' => false],
                ['url' => asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js'), 'isModule' => false],
                ['url' => asset('plugins/summernote/summernote-bs4.min.js'), 'isModule' => false],
                ['url' => asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js'), 'isModule' => false],
                ['url' => asset('plugins/jquery-validation/jquery.validate.min.js'), 'isModule' => false],
                ['url' => asset('plugins/jquery-validation/additional-methods.min.js'), 'isModule' => false],
                ['url' => asset('plugins/select2/js/select2.full.min.js'), 'isModule' => false],
                ['url' => asset('plugins/datatables/jquery.dataTables.js'), 'isModule' => false],
                ['url' => asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js'), 'isModule' => false],
                ['url' => asset('plugins/datatables-responsive/js/dataTables.responsive.min.js'), 'isModule' => false],
                ['url' => asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js'), 'isModule' => false],
                ['url' => asset('plugins/pace-progress/pace.min.js'), 'isModule' => false],
                ['url' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.js', 'isModule' => false],
                ['url' => 'https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js', 'isModule' => false],
                ['url' => 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js', 'isModule' => false],
                ['url' => 'https://unpkg.com/@popperjs/core@2', 'isModule' => false],
                ['url' => 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js', 'isModule' => false],
                ['url' => 'https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js', 'isModule' => false],
                ['url' => asset('js/adminlte.min.js'), 'isModule' => false],
            ],
            'styles' => [
                asset('plugins/fontawesome-free/css/all.min.css'),
                'https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css',
                asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css'),
                asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css'),
                asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css'),
                asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css'),
                asset('plugins/select2/css/select2.min.css'),
                asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css'),
                'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.css',
                'https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css',
                '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
                'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css',
                'https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css',
                'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700',
                asset('css/adminlte.min.css'),
                asset('css/style.css'),
                asset('css/attend.css'),
                asset('css/toaster.css'),
            ],
        ];
    }

    // Module-specific assets
    protected static function getModuleAssets($module)
    {
        $modules = [
            'dashboard' => [
                'scripts' => [
                    ['url' => asset('js/pages/dashboard.js'), 'isModule' => false],
                ],
                'styles' => [],
            ],
            'sales' => [
                'scripts' => [
                    ['url' => asset('js/common/sales_utilities.js'), 'isModule' => true],
                    ['url' => asset('js/common/global.js'), 'isModule' => false],
                    ['url' => asset('js/sales/sale_calculations.js'), 'isModule' => true],
                    ['url' => asset('js/sales/new_sale.js'), 'isModule' => true],
                ],
                'styles' => [
                    asset('css/sale/sales.css'),
                ],
            ],
        ];

        return $modules[$module] ?? ['scripts' => [], 'styles' => []];
    }

    // Get assets based on module
    public static function getAssets($module = null)
    {
        // Fetch common assets
        $commonAssets = self::getCommonAssets();

        if ($module) {
            // Fetch module-specific assets
            $moduleAssets = self::getModuleAssets($module);

            // Merge scripts while keeping the `isModule` property intact
            $mergedScripts = array_merge(
                $commonAssets['scripts'],
                $moduleAssets['scripts']
            );

            // Merge styles
            $mergedStyles = array_unique(array_merge($commonAssets['styles'], $moduleAssets['styles']));

            return [
                'scripts' => $mergedScripts,
                'styles' => array_values($mergedStyles),
            ];
        }

        // Return only common assets if no module is specified
        return [
            'scripts' => $commonAssets['scripts'],
            'styles' => $commonAssets['styles'],
        ];
    }
}
