<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Category;
use App\Group;
use App\Question;
use App\QuestionQuestionOption;

class QuestionGeneratorController extends Controller
{
    private $c = array();

    public function cloneYearQuestions(Request $request)
    {
        if ($request->has('fromYear') && $request->has('toYear')) {
            $fromYear = $request->input('fromYear');
            $toYear = $request->input('toYear');

            $categories = Category::where("year_id", "=", $fromYear)
                ->get();

            foreach ($categories as $category) {
                if ($category->question_id) {
                    array_push($this->c, array(
                        'oldCategory' => $category->id,
                        'oldQuestion' => $category->question_id
                    ));
                }
            }

            foreach ($categories as $category) {

                $newCategory = new Category();

                $newCategory->name = $category->name;
                $newCategory->year_id = $toYear;
                $newCategory->name = $category->name;
                $newCategory->condition = $category->condition;
                $newCategory->sort = $category->sort;
                $newCategory->icon = $category->icon;

                $newCategory->save();

                for ($i = 0; $i < count($this->c); $i++) {
                    if ($this->c[$i]['oldCategory'] == $category->id) {
                        $this->c[$i]['newCategory'] = $newCategory->id;
                    }
                }

                $groups = $category
                    ->getGroups()
                    ->get();

                foreach ($groups as $group) {
                    $newGroup = new Group();

                    $newGroup->category_id = $newCategory->id;
                    $newGroup->name = $group->name;
                    $newGroup->tiptext = $group->tiptext;
                    $newGroup->sort = $group->sort;

                    $newGroup->save();

                    $questions = $group
                        ->getQuestions()
                        ->whereNull('parent')
                        ->get();

                    $this->getQuestions($questions, $newGroup, $fromYear, $toYear, null);
                }
            }

            foreach ($this->c as $d) {
                Category::where('id', "=", $d['newCategory'])
                    ->update(
                        [
                            'question_id' => $d['newQuestion']
                        ]);
            }

            return new Response("Clone Successful");
        } else {
            return new Response("Please specifiy the 'fromYear' and 'toYear' parameter.");
        }
    }

    private function getQuestions($questions, $group, $fromYear, $toYear, $parentQuestion)
    {
        foreach ($questions as $question) {
            $questionText = $question->text;
            if (strpos($question->text, (string)$fromYear) !== false) {
                $questionText = str_replace((string)$fromYear, (string)$toYear, $question->text);
            }

            $questionTipText = $question->tip_text;
            if (strpos($question->tip_text, (string)$fromYear) !== false) {
                $questionTipText = str_replace((string)$fromYear, (string)$toYear, $question->tip_text);
            }

            $questionPlaceholder = $question->placeholder_text;
            if (strpos($question->placeholder_text, (string)$fromYear) !== false) {
                $questionPlaceholder = str_replace((string)$fromYear, (string)$toYear, $question->placeholder_text);
            }

            $newQuestion = new Question();

            $newQuestion->text = $questionText;
            $newQuestion->answer_option = $question->answer_option;
            $newQuestion->parent = $parentQuestion ? $parentQuestion->id : null;
            $newQuestion->group_id = $group->id;
            $newQuestion->condition = $question->condition;
            $newQuestion->type = $question->type;
            $newQuestion->validation_type = $question->validation_type;
            $newQuestion->has_childs = $question->has_childs;
            $newQuestion->question_genre_id = $question->question_genre_id;
            $newQuestion->sort = $question->sort;
            $newQuestion->tip_text = $questionTipText;
            $newQuestion->placeholder_text = $questionPlaceholder;
            $newQuestion->profile_question_id = $question->profile_question_id;

            $newQuestion->save();

            for ($i = 0; $i < count($this->c); $i++) {
                if ($this->c[$i]['oldQuestion'] == $question->id) {
                    $this->c[$i]['newQuestion'] = $newQuestion->id;
                }
            }

            $options = $question
                ->getOptions()
                ->get();

            foreach ($options as $option) {
                $newQuestionQuestionOption = new QuestionQuestionOption();

                $newQuestionQuestionOption->question_id = $newQuestion->id;
                $newQuestionQuestionOption->question_option_id = $option->id;

                $newQuestionQuestionOption->save();
            }

            if ($question->has_childs == 1) {
                $childQuestions = Question::join('group', 'question.group_id', 'group.id')
                    ->join('category', 'group.category_id', 'category.id')
                    ->where('category.year_id', '=', $fromYear)
                    ->where('question.parent', '=', $question->id)
                    ->select('question.*')
                    ->get();

                $this->getQuestions($childQuestions, $group, $fromYear, $toYear, $newQuestion);
            }
        }
    }
}