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
    const uploadTaxReturn = 9;
    const taxReturnUploaded = 10;
    const finalTaxAssesmentUploaded =11;
}