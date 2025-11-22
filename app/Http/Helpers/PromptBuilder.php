<?php 

namespace App\Http\Helpers;

class PromptBuilder {

    public static function buildPrompt($shotType, $productDescription = 'product'){

        $shotConfig = config("shot_types.{$shotType}");

        if(!$shotConfig) {

            return "Professional product photography of {$productDescription}";
        }
        return sprintf(
            "Professional %s photography of %s. Shot with %s, %s, %s composition. %s mood.",
            $shotConfig['name'],
            $productDescription,
            $shotConfig['camera_angle'],
            $shotConfig['lighting'],
            $shotConfig['composition'],
            $shotConfig['mood']
        );


    
}
}

?>