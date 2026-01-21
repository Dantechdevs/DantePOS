<?php

namespace App\Http\Helpers;

class ProductHelper
{
    public static function calculateSoldAndRemaining($totalPiecesSold, $piecesPerBox)
    {
        // Calculate number of boxes sold
        $boxesSold = (int) ($totalPiecesSold / $piecesPerBox);

        // Calculate remaining pieces
        $remainingPieces = $totalPiecesSold % $piecesPerBox;

        return [
            'boxes_sold' => $boxesSold,
            'items_sold' => $remainingPieces,
        ];
    }

    public static function calculateBoxesAndRemaining($stockCount, $bottlesPerBox) {
        // Calculate the number of full boxes
        $fullBoxes = floor($stockCount / $bottlesPerBox);

        // Calculate the remaining bottles
        $remainingBottles = $stockCount % $bottlesPerBox;

        return [
            'fullBoxes' => $fullBoxes,
            'remainingItems' => $remainingBottles
        ];
    }
}
