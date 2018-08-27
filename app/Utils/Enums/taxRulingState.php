<?php
namespace App\utils\enums;
abstract class TaxRulingState {
    const notStarted = 0;
    const started = 1;
    const readyToReview = 2;
    const notAccepted = 3;
    const modified = 4;
    const approved = 5;
    const acceptedByGoverment = 6;
    const notAcceptedByGoverment = 7;
}