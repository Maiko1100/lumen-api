<?php

namespace App\Http\Controllers;

use App\UserFile;
use App\UserQuestion;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use App\UserYear as UserYear;
use App\Person;
use Illuminate\Support\Facades\DB;
use App\Utils\Enums\ProgressState;
use App\Utils\Enums\userRole;
use App\Utils\Enums\documentType;

class UserFileController extends Controller
{
    public function getFiles()
    {
        $user = JWTAuth::parseToken()->authenticate();


        $files = UserFile::where('user_file.person_id', '=', $user->person_id)->get();
        return $files;
    }
    public function getTaxReturnFiles()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $files = UserFile::where('user_file.person_id', '=', $user->person_id)
            ->join("user_year", "user_file.user_year_id", "user_year.id")
            ->select('user_year.year_id','user_year.person_id as id','user_file.person_id','user_file.id', 'user_file.user_question_id', 'user_file.name', 'user_file.type', 'user_file.description', 'user_file.question_id', 'user_file.user_year_id', 'user_file.qpid')->get();
        return $files;
    }


    public function getFile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userYear =$request->input('userYear');

        if (isset($userYear)) {
            if ($user->role == '2' || '3') {
                $personId = UserYear::where('id', '=', $userYear)->first()->person_id;
                $filename = $request->input('fileName');
                $fullpath = "app/userDocuments/{$personId}/{$filename}";
//                var_dump($fullpath);
//                exit;
                return response()->download(storage_path($fullpath), null, [], null);
            } else {
                return response('Unauthorized.', 401);
            }
        }
            $personId = $user->person_id;
            $filename = $request->input('fileName');
            $fullpath = "app/userDocuments/{$personId}/{$filename}";


        return response()->download(storage_path($fullpath), null, [], null);

    }


    public function deleteFile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $personId = $user->person_id;
        $filename = $request->input('fileName');
        $fullpath = "userDocuments/{$personId}/{$filename}";

        $file = UserFile::where('person_id','=',$personId)->where('name','=',$filename)->first();
        $file->delete();

        if (Storage::delete($fullpath)) {
            $userfile = UserFile::where("user_year.person_id", "=", $user->person_id)->where('name', "=", $filename)
                ->join("user_year", "user_file.user_year_id", "user_year.id");
            $userfile->delete();
            return $user->getUserFiles();
        } else {
            return new Response('file does not exist');
        }
    }

    public function deleteQuestionFile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $personId = $user->person_id;
        $filename = $request->input('fileName');
        $fullpath = "userDocuments/{$personId}/{$filename}";

        if (Storage::delete($fullpath)) {
            $userfile = UserFile::where("person_id", "=", $personId)
                ->where('name', "=", $filename)
                ->first();
            $uqid = $userfile->user_question_id;
            UserFile::where("person_id", "=", $personId)
                ->where('name', "=", $filename)
                ->delete();

            $newFiles = [];
            $files = UserFile::where("user_question_id", "=", $uqid)
                ->select('name')
                ->get();

            foreach ($files as $file) {
                array_push($newFiles, $file->name);
            }

            return $newFiles;
        } else {
            return new Response('file does not exist');
        }
    }

    public function saveQuestionFile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $qpid = $request->input('qpid');

        $userQuestioncontroller = new UserQuestionController();

        $uqid = (int)$userQuestioncontroller->save($request);
        foreach ($request->file('files') as $file){
            $pinfo = pathinfo($file->getClientOriginalName());
            $newName = $pinfo['filename'] . "_" . date("YmdHis") . "." . $pinfo['extension'];
            Storage::putFileAs('userDocuments/' . $user->person_id, $file, $newName);

            $userFile = new UserFile();
            $userFile->user_question_id = $uqid;
            $userFile->person_id = $user->person_id;
            $userFile->name = $newName;
            $userFile->type = documentType::questionaire_documents;

            if(isset($qpid)) {
                $userFile->qpid = $qpid;
            }

            $userFile->save();
        }

        $newFiles = [];
        $files = UserFile::where("user_question_id", "=", $uqid)
            ->select('name')
            ->get();

        foreach ($files as $file) {
            array_push($newFiles, $file->name);
        }

        return new Response($newFiles);
    }

    public function save(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $year = $request->input('year');
        $questionId = $request->input('id');
        $answer = $request->input('answer');
        $qpid = $request->input('qpid');
        $userYear = UserYear::where("person_id", "=", $user->person_id)
            ->where("year_id", "=", $year)->first();

        $isProfile = Question::where("id", "=", $questionId)
                ->first()
                ->getGenre()
                ->first()
                ->isProfile == 1;

        if (isset($qpid)) {

            if (isset($isProfile)) {
                $existingQuestion = $this->checkPlus($questionId, $qpid, $userYear);
            } else {
                $existingQuestion = $this->checkPlus($questionId, $qpid);
            }

            if (isset($existingQuestion)) {
                $existingQuestion->question_answer = $answer;
                $existingQuestion->save();
            } else {
                $userQuestion = new UserQuestion();
                $userQuestion->person_id = $user->person_id;
                $userQuestion->user_year_id = $userYear->id;
                $userQuestion->question_id = $questionId;
                $userQuestion->question_answer = $answer;
                $userQuestion->question_plus_id = $qpid;

                $userQuestion->save();
            }
        } else {
            $existingQuestion = $this->check($userYear, $questionId);
            if (isset($existingQuestion)) {
                $existingQuestion->question_answer = $answer;
                $existingQuestion->save();
            } else {
                $userQuestion = new UserQuestion();
                $userQuestion->person_id = $user->person_id;
                $userQuestion->user_year_id = $userYear->id;
                $userQuestion->question_id = $questionId;
                $userQuestion->question_answer = $answer;

                $userQuestion->save();
            }
        }

        return new Response($year);

    }

    private function check($userYear, $questionId)
    {
        return UserQuestion::where("user_year_id", "=", $userYear->id)->where("question_id", "=", $questionId)->first();
    }

    private function checkPlus($questionId, $qpid, $userYear = NULL)
    {
        if (isset($userYear)) {
            return UserQuestion::where("question_id", "=", $questionId)
                ->where("question_plus_id", "=", $qpid)
                ->where("user_year_id", "=", $userYear->id)
                ->first();
        } else {
            return UserQuestion::where("question_id", "=", $questionId)
                ->where("question_plus_id", "=", $qpid)
                ->where("user_year_id", "IS", "NULL")
                ->first();
        }
    }

    public function saveFile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $userFile = new UserFile();
        $userFile->name = $fileName;
        $request->input('year');


        $userYear = UserYear::where('user_year.person_id', "=", $user->person_id)->where("user_year.year_id", "=", $request->input('year'))->first();

        Storage::putFileAs('test' . $user->person_id."/", $file, $fileName);

        $userFile->type = documentType::normal_documents;
        $userFile->person_id = $user->person_id;
        $userFile->user_year_id = $userYear->id;
        $userFile->save();

        return $user->getUserFiles();

    }

    public function getReport(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userYear = UserYear::where('user_year.person_id', "=", $user->person_id)->where("user_year.year_id", "=", $request->input('year'))->first();
        $personId = $user->person_id;
        $userFile = UserFile::where('user_year_id', '=', $userYear->id)->where('type', '=', documentType::report)->first();
        $filename = $userFile->name;
        $fullpath = "app/userDocuments/{$personId}/{$filename}";

        return response()->download(storage_path($fullpath), null, [], null);
    }

    public function saveReport(Request $request)
    {
        $file = $request->file('file');
        $person_id = $request->input('person_id');
        $userYear = UserYear::where('user_year.person_id', "=", $person_id)->where("user_year.year_id", "=", $request->input('year'))->first();
        $person = Person::where('id','=',$person_id)->first();
        $fileName = 'TaxReport'.'_'.$person->first_name.'_'.$person->last_name.'_'.$request->input('year').'.pdf';

        Storage::putFileAs('userDocuments/' . $person_id. "/", $file, $fileName);

        $userFile = new UserFile();
        $userFile->name = $fileName;
        $userFile->type = documentType::report;
        $userFile->person_id = $person_id;
        $userFile->user_year_id = $userYear->id;
        $userFile->save();
        $userYear->status = ProgressState::reportUploaded;
        $userYear->save();
        MailController::sendStatusMail($userYear);
        $cases = DB::table('user_year')
            ->join('person', 'user_year.person_id', '=', 'person.id')
            ->leftjoin('person as employee', 'user_year.employee_id', '=', 'employee.id')
            ->select('employee.first_name as employee_name','user_year.year_id', 'user_year.package', 'user_year.status', 'user_year.id', 'user_year.employee_id', 'person.id as person_id', 'person.first_name', 'person.last_name', 'person.passport', 'person.bsn', 'person.dob')->get();
        return $cases;

    }
    public function saveFinalTaxAssessment(Request $request)
    {
        $file = $request->file('file');
        $person_id = $request->input('person_id');
        $userYear = UserYear::where('user_year.person_id', "=", $person_id)->where("user_year.year_id", "=", $request->input('year'))->first();
        $person = Person::where('id','=',$person_id)->first();
        $fileName = 'FinalTaxAssessment'.'_'.$person->first_name.'_'.$person->last_name.'_'.$request->input('year').'.pdf';
        Storage::putFileAs('userDocuments/' . $person_id, $file, $fileName);

        $userFile = new UserFile();
        $userFile->name = $fileName;
        $userFile->type = documentType::final_tax_assesment;
        $userFile->person_id = $person_id;
        $userFile->user_year_id = $userYear->id;
        $userFile->save();
        $userYear->status = ProgressState::finalTaxAssessmentUploaded;
        $userYear->save();
        MailController::sendStatusMail($userYear);

        $cases = DB::table('user_year')
            ->join('person', 'user_year.person_id', '=', 'person.id')
            ->leftjoin('person as employee', 'user_year.employee_id', '=', 'employee.id')
            ->select('employee.first_name as employee_name','user_year.year_id', 'user_year.package', 'user_year.status', 'user_year.id', 'user_year.employee_id', 'person.id as person_id', 'person.first_name', 'person.last_name', 'person.passport', 'person.bsn', 'person.dob')->get();
        return $cases;

    }
    public function getFinalTaxAssessment(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userYear = UserYear::where('user_year.person_id', "=", $user->person_id)->where("user_year.year_id", "=", $request->input('year'))->first();
        $personId = $user->person_id;
        $userFile = UserFile::where('user_year_id', '=', $userYear->id)->where('type', '=', documentType::final_tax_assesment)->first();
        $filename = $userFile->name;
        $fullpath = "app/userDocuments/{$personId}/{$filename}";

        return response()->download(storage_path($fullpath), null, [], null);
    }
    public function savePreliminaryTax(Request $request)
    {
        $file = $request->file('file');
        $person_id = $request->input('person_id');
        $userYear = UserYear::where('user_year.person_id', "=", $person_id)->where("user_year.year_id", "=", $request->input('year'))->first();
        $person = Person::where('id','=',$person_id)->first();
        $fileName = 'PreliminaryTax'.'_'.$person->first_name.'_'.$person->last_name.'_'.$request->input('year').'.pdf';
        Storage::putFileAs('userDocuments/' . $person_id, $file, $fileName);

        $userFile = new UserFile();
        $userFile->name = $fileName;
        $userFile->type = documentType::preliminaryTax;
        $userFile->person_id = $person_id;
        $userFile->user_year_id = $userYear->id;
        $userFile->save();
        $userYear->status = ProgressState::preliminaryTaxUploaded;
        $userYear->save();
        MailController::sendStatusMail($userYear);

        $cases = DB::table('user_year')
            ->join('person', 'user_year.person_id', '=', 'person.id')
            ->leftjoin('person as employee', 'user_year.employee_id', '=', 'employee.id')
            ->select('employee.first_name as employee_name','user_year.year_id', 'user_year.package', 'user_year.status', 'user_year.id', 'user_year.employee_id', 'person.id as person_id', 'person.first_name', 'person.last_name', 'person.passport', 'person.bsn', 'person.dob')->get();
        return $cases;

    }
    public function getPrilimentaryTax(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userYear = UserYear::where('user_year.person_id', "=", $user->person_id)->where("user_year.year_id", "=", $request->input('year'))->first();
        $personId = $user->person_id;
        $userFile = UserFile::where('user_year_id', '=', $userYear->id)->where('type', '=', documentType::preliminaryTax)->first();
        $filename = $userFile->name;
        $fullpath = "app/userDocuments/{$personId}/{$filename}";

        return response()->download(storage_path($fullpath), null, [], null);
    }
}

?>