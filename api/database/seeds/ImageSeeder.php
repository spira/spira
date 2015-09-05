<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Image;
use Illuminate\Support\Facades\App;
use App\Services\Cloudinary;

class ImageSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /*
         * if we're in the travis ci environment, don't use the cloudinary remote images as the tests are the
         * same, and it uses up connection quota unnecessarily
         */
        if (getenv('TRAVIS')) {
            $this->command->comment('TravisCI environment, seeding mock images');
            factory(Image::class, 5)->create();

            return;
        }

        try {
            $this->seedFromCloudinary();
        } catch (Cloudinary\Api\Error $e) {
            $this->command->error('Cloudinary Error: '.$e->getMessage());
            $this->command->comment('Unable to seed images from cloudinary, falling back to mock images');
            //create & link images
            factory(Image::class, 5)->create();
        }
    }

    /**
     * Seed the images from remote cloudiary api.
     * @throws Cloudinary\Api\Error
     */
    private function seedFromCloudinary()
    {
        /** @var Cloudinary $cloudinary */
        $cloudinary = App::make(Cloudinary::class);

        $remoteImageResponse = $cloudinary->getRemoteImages();

        $images = $remoteImageResponse->storage['resources'];

        $this->command->comment(count($images) . " images retrieved from Cloudinary");

        foreach ($images as $image) {
            $imageId = $image['public_id'];
            if (! \Rhumsaa\Uuid\Uuid::isValid($imageId)) {
                continue; //skip the non-uuid images (like the sample image)
            }

            factory(Image::class)->create(
                [
                    'image_id' => $imageId,
                    'version' => $image['version'],
                    'format' => $image['format'],
                ]
            );
        }
    }
}
