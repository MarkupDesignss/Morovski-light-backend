<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faqs';
    protected $fillable = [
        'question',
        'question_de',
        'answer',
        'answer_de',
        'sort_order',
        'is_active',
    ];

    public function getQuestionAttribute()
    {
        if (app()->getLocale() === 'de' && !empty($this->attributes['question_de'])) {
            return $this->attributes['question_de'];
        }

        return $this->attributes['question'] ?? null;
    }

    public function getAnswerAttribute()
    {
        if (app()->getLocale() === 'de' && !empty($this->attributes['answer_de'])) {
            return $this->attributes['answer_de'];
        }

        return $this->attributes['answer'] ?? null;
    }

    public function getQuestionTranslatedAttribute()
    {
        if (app()->getLocale() === 'de' && !empty($this->question_de)) {
            return $this->question_de;
        }

        return $this->question;
    }
    public function getAnswerTranslatedAttribute()
    {
        if (app()->getLocale() === 'de' && !empty($this->answer_de)) {
            return $this->answer_de;
        }

        return $this->answer;
    }
}
