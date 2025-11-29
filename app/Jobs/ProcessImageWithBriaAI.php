<?php

namespace App\Jobs;

use App\Models\BrandPreset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\GeneratedImage;

class ProcessImageWithBriaAI implements ShouldQueue
{
    use Queueable,InteractsWithQueue,SerializesModels,Dispatchable;

    public $userId;
    public $presetsId;
    public $imagePaths;
    public $description;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $presetsId, $imagePaths, $description)
    {
        $this->userId = $userId;
        $this->imagePaths = $imagePaths;
        $this->presetsId = $presetsId;
        $this->description = $description;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the presets
        $preset = BrandPreset::find($this->presetsId);

        // exception
        if(!$preset){
            Log::error("Preset not found: {$this->presetsId}");
            return;
        }

        $apiKey = env('BRIA_API_KEY');

        //process each image
        foreach($this->imagePaths as $index => $productPath){

            try{

                $description = $this->description[$index] ?? 'product';
                $newPrompt = $this->buildPromptFromPreset($preset, $description);
            // Call Bria API
                $response = HTTP::withHeaders([
                    'api_token' => $apiKey,
                    'Content_type' => 'application/json'
                ])->post('https://engine.prod.bria-api.com/v2/image/generate', [
                    'prompt' => $newPrompt,
                    'num_results' => 1,
                    'sync' => true
                ]);

                if(!$response->successful()){
                    Log::error("Bria API error for image {$index}: " . json_encode($response->json()));
                    continue;
                }

                // save to database
                 $briaData = $response->json();

                //save to database
                GeneratedImage::create([

                    'user_id' => $this->userId,
                    'brand_preset_id' => $preset->id,
                    'product_image_path' => $productPath,
                    'user_intent' => $description,
                    'structured_prompt' => $briaData['result']['structured_prompt'], 
                    'generated_image_url' => $briaData['result']['image_url'],
                    'shot_type' => $preset->shot_type,
                    'style' => $preset->structured_prompt['style'] ?? 'default',
                    'angle' => $preset->structured_prompt['angle'] ?? 'default',
                    'bria_request_id' => $briaData['request_id'],
                    'metadata' => $briaData
                ]);

                Log::info("Successfully processed image {$index} for user {$this->userId} with preset {$this->presetsId}");


            }catch(\Exception $e){
                Log::error("Error processing image {$index}: " . $e->getMessage());
                continue;
            }
        }
    }

    
    // Helper method to build prompt from preset
    private function buildPromptFromPreset($preset, $productDescription)
    {
        // Get shot type config
        $shotConfig = config("shot_types.{$preset->shot_type}");
        
        if (!$shotConfig) {
            return "Professional product photography of {$productDescription}";
        }

        // Build prompt using preset's shot type settings + new product
        return sprintf(
            "Professional %s photography of %s. Shot with %s, %s, %s composition. %s mood and atmosphere.",
            $shotConfig['name'],
            $productDescription,
            $shotConfig['camera_angle'],
            $shotConfig['lighting'],
            $shotConfig['composition'],
            $shotConfig['mood']
        );
    }

}
