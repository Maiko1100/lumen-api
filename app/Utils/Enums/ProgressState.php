<?php
namespace App\utils\enums;
abstract class ProgressState {
    const questionnaireStartedNotPaid = 0;
    const questionnaireSubmittedNotPaid = 1;
    const questionnaireStartedPaid = 2;
    const questionnaireReadyToReview = 3;
    const questionnaireNotApproved = 4;
    const questionnaireModified = 5;
    const questionnaireApproved = 6;
    const reportUploaded = 7;
    const reportNotApproved = 8;
    const fileTaxReturn = 9;
    const taxReturnFiled = 10;
    const preliminaryTaxUploaded = 11;
    const finalTaxAssessmentUploaded =12;
}

