<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use App\Sentence;

class SentenceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        $prev_sent_id = -1;
        Sentence::truncate();
        for($i = 1 ; $i <= App\Story::count(); $i++){
            $cur_story = App\Story::find($i);
            $first_sent = Sentence::create([
                'text' => $faker->sentence($nbWords = 8, $variableNbWords = true),
                'author_id' => $cur_story->user_id,
                'prev_sentence_id' => NULL,
                'story_id' => $i
            ]);
            $prev_sent_id = $first_sent->id;
            $newSent = NULL;
            for($j = 1; $j<= rand(1,50); $j++){
                $newSent = Sentence::create([
                    'text' => $faker->sentence($nbWords = 8, $variableNbWords = true),
                    'author_id' => rand(1,App\User::count()),
                    'prev_sentence_id' => $prev_sent_id,
                    'story_id' => $i
                ]);
                $prev_sent_id = $newSent->id;
            }
            if($newSent->author_id===$cur_story->user_id){
                $cur_story->finished = True;
                $cur_story->save();
            }
            $prev_sent_id = -1;
        }
    }
}
