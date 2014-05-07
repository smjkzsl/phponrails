<?php

require_once(dirname(__FILE__).'/../config.php');

class Image_TestCase extends ActiveSupportUnitTest
{
    public function __construct() {
        parent::__construct();
        $this->fixtures_base = AkConfig::getDir('fixtures').DS.'Image_TestCase';
        if(!($this->offline_mode = !(@file_get_contents('https://avatars3.githubusercontent.com/u/27074?s=140')))){
            $this->image_path = $this->fixtures_base.DS.'rails_framework_logo.png';
            $this->photo_path = $this->fixtures_base.DS.'cristobal.jpg';
            $this->watermark = $this->fixtures_base.DS.'watermark.png';

            AkFileSystem::copy(AkConfig::getDir('fixtures').'/old_logo.png', $this->image_path, array('base_path'=>RAILS_DIR));
            $cristobal = @Ak::url_get_contents('https://avatars3.githubusercontent.com/u/27074?s=140', array('cache'=>100000));
            if(!empty($cristobal)) AkFileSystem::file_put_contents($this->photo_path, $cristobal, array('base_path'=>RAILS_DIR));
            $watermark = @Ak::url_get_contents('https://avatars3.githubusercontent.com/u/27074?s=140', array('cache'=>100000));
            if(!empty($watermark)) AkFileSystem::file_put_contents($this->watermark, $watermark, array('base_path'=>RAILS_DIR));
            $this->_run_extra_tests = file_exists($this->photo_path);
        }
    }

    public function __destruct() {
        AkFileSystem::directory_delete($this->fixtures_base, array('base_path'=>RAILS_DIR));
    }

    public function skip(){

        $this->skipIf(!function_exists('gd_info'), '['.get_class($this).'] GD is not available.');
        $this->skipIf($this->offline_mode, '['.get_class($this).'] Internet connection unavailable, can\'t download remote images.');
    }

    public function test_image_save_as() {
        $PngImage = new AkImage($this->image_path);
        $this->assertEqual($PngImage->getExtension(), 'png');

        $PngImage->save($this->image_path.'.jpg', 100, array('base_path'=>RAILS_DIR));
        $JpgImage = new AkImage($this->image_path.'.jpg');
        $this->assertEqual($JpgImage->getExtension(), 'jpg');

        $PngImage = new AkImage($this->image_path);
        $PngImage->save($this->image_path.'.gif', 100, array('base_path'=>RAILS_DIR));
        $GifImage = new AkImage($this->image_path.'.gif');
        $this->assertEqual($GifImage->getExtension(), 'gif');
    }
   
    public function test_image_resize() {
        $Image = new AkImage();
        $Image->load($this->image_path);

        $this->assertEqual($Image->getWidth(), 170);
        $this->assertEqual($Image->getHeight(), 75);


        $Image->transform('resize',array('size'=>'50x'));
        $Image->save($this->image_path.'_50x22.jpg', 100, array('base_path'=>RAILS_DIR));

        $Image = new AkImage($this->image_path.'_50x22.jpg');
        $this->assertEqual($Image->getWidth(), 50);
        $this->assertEqual($Image->getHeight(), 22);


        $Image = new AkImage($this->image_path);
        $Image->transform('resize',array('size'=>'50%'));
        $Image->save($this->image_path.'_85x37.png', 100, array('base_path'=>RAILS_DIR));

        $Image = new AkImage($this->image_path.'_85x37.png');
        $this->assertEqual($Image->getWidth(), 85);
        $this->assertEqual($Image->getHeight(), 37);


        $Image = new AkImage($this->image_path);
        $Image->transform('resize', array('mode'=>'force','size'=>'300x300'));
        $Image->save($this->image_path.'_300x300.png', 100, array('base_path'=>RAILS_DIR));

        $Image = new AkImage($this->image_path.'_300x300.png');
        $this->assertEqual($Image->getWidth(), 300);
        $this->assertEqual($Image->getHeight(), 300);


        $Image = new AkImage($this->image_path);
        $Image->transform('resize', array('mode'=>'expand','size'=>'x300'));
        $Image->save($this->image_path.'_x300.png', 100, array('base_path'=>RAILS_DIR));


        $Image = new AkImage($this->image_path.'_x300.png');
        $this->assertEqual($Image->getWidth(), 680);
        $this->assertEqual($Image->getHeight(), 300);


        $Image = new AkImage($this->image_path);
        $Image->transform('resize', array('mode'=>'expand','size'=>'300x300'));
        $Image->save($this->image_path.'_680x300.png', 100, array('base_path'=>RAILS_DIR));


        $Image = new AkImage($this->image_path.'_680x300.png');
        $this->assertEqual($Image->getWidth(), 680);
        $this->assertEqual($Image->getHeight(), 300);


        $Image = new AkImage($this->image_path);
        $Image->transform('resize', array('mode'=>'expand','size'=>'200%'));
        $Image->save($this->image_path.'_340x150.png', 100, array('base_path'=>RAILS_DIR));


        $Image = new AkImage($this->image_path.'_340x150.png');
        $this->assertEqual($Image->getWidth(), 340);
        $this->assertEqual($Image->getHeight(), 150);
    }


    public function test_image_crop() {
        if(!$this->_run_extra_tests) return;

        $Image = new AkImage();
        $Image->load($this->photo_path);

        $Image->transform('crop',array('x'=>20, 'y'=>0, 'size'=>'30x30'));
        $Image->save($this->photo_path.'_30x30_crop.jpg', 100, array('base_path'=>RAILS_DIR));

        $Image = new AkImage($this->photo_path.'_30x30_crop.jpg');
        $this->assertEqual($Image->getWidth(), 30);
        $this->assertEqual($Image->getHeight(), 30);

        $Image = new AkImage();
        $Image->load($this->photo_path);

        $Image->transform('crop',array('x'=>20, 'y'=>15, 'width'=>50));
        $Image->save($this->photo_path.'_50_crop.jpg', 100, array('base_path'=>RAILS_DIR));

        $Image = new AkImage($this->photo_path.'_50_crop.jpg');
        $this->assertEqual($Image->getWidth(), 50);
        $this->assertEqual($Image->getHeight(), 359);

        $Image = new AkImage();
        $Image->load($this->photo_path);

        $Image->transform('crop',array('x'=>0, 'y'=>15));
        $Image->save($this->photo_path.'top_crop.jpg', 100, array('base_path'=>RAILS_DIR));

        $Image = new AkImage($this->photo_path.'top_crop.jpg');
        $this->assertEqual($Image->getWidth(), 499);
        $this->assertEqual($Image->getHeight(), 359);
    }

    public function test_image_watermark() {
        if(!$this->_run_extra_tests) return;

        $Image = new AkImage();
        $Image->load($this->photo_path);
        $Image->transform('watermark',array('mark'=>$this->watermark));
        $Image->save($this->photo_path.'_watermarked.jpg', 100, array('base_path'=>RAILS_DIR));
        
        $possible_watermarks = array(
            '0ed190e6633546a610d57db9f3da72b8',
            '234adf4a48224f8596e53d665bf41768',
            'a26ad317083f831458e0e00b617786bd'
            );
        $this->assertTrue(in_array(md5_file($this->photo_path.'_watermarked.jpg'), $possible_watermarks));
    }


    public function test_should_apply_native_filters() {
        $native_filters = array(
        'negate' =>         array('params' => array(),          'hashes' => '8b44f26c9646ac69a1b48bbc66622184,54a20406d35057aeee0ee82675756af5,20702af44b9b6e796cb752fe64777cfe'),
        'grayscale' =>      array('params' => array(),          'hashes' => 'd08a0ad61f4fd5b343c0a4af6d810ddf,0336373befa9948d9ff7bab6b40a2f54,913a5a05d89c5973cf7155e0170397f0'),
        'brightness' =>     array('params' => 50,               'hashes' => '1e38de2377e42848cae326de52a75252,2611e585d23079155a75fff36d91a8f6,f0cf541e1ba8a9f7b0a9d8386f64c8fb'),
        'contrast' =>       array('params' => 50,               'hashes' => 'ded57ff56253afb0efd4e09c17d44efb,66c9e9d6cf1da2657ff18c9bb6b0ffe5,2cf57abda9373882b89feb6f665ce45d'),
        'colorize' =>       array('params' => array(100,25,30), 'hashes' => 'ddcb214d2e9c0c6c7d58a9bb0ce09b4a,2458a17a722333ca897b564e76b202ed,e021098d74dfa3a6e12e11f3a4e25492'),
        'detect_edges' =>   array('params' => array(),          'hashes' => '4c5f8c9f54917b66ecea8631aabb0e85,f6182e2243644d3c14e351c0ff377623,4f27b0b316152dfdb10b921ac3b22b3c'),
        'emboss' =>         array('params' => array(),          'hashes' => 'a3edb232afbd5d9e210172a40abec35e,41a803cc182532c56d66d198f33b296c,8ce6eed4fa060d3730f3718f6e0e2ed0'),
        'gaussian_blur' =>  array('params' => array(),          'hashes' => 'd1d2ba1995dff5b7c638d85d968d070a,ba2b487e4f45c5e80811203110928834,e9e9fa440f60d59dee4428b35737f33c'),
        'selective_blur' => array('params' => array(),          'hashes' => 'b68b972fc7d29d3a4942f2057ab085f2,771cf4c6cba109c03923ed8e4f80989a,72e419aab622a7d037c6e378167e197b'),
        'sketch' =>         array('params' => array(),          'hashes' => '63d0dd06515c4ec72f8dc5fc9de74d8e,0a2e5fec442f8a7b06cbb8a06fbaf32b,f0249953d159d4c2989c20d0604e8253'),
        'smooth' =>         array('params' => 5,                'hashes' => '6158f362febe3b7b9add756c9d5acf2c,ec28f771c343ed70f0445bdcc666e319,da52d906f99879737b5c318c0b02bd87'),
        );

        foreach ($native_filters as $native_filter => $options){
            $Image = new AkImage();
            $Image->load($this->photo_path);
            if($Image->isNativeFiler($native_filter)){
                $Image->transform($native_filter, $options['params']);
                $image_path = $this->photo_path."_$native_filter.jpg";
                $Image->save($image_path, 100, array('base_path'=>RAILS_DIR));
                $this->assertTrue(in_array(md5_file($image_path), explode(',', $options['hashes'])), "$native_filter failed, we expected a checksum in ".$options['hashes'].' and got '.md5_file($image_path));
            }
        }
    }

}

ak_test_case('Image_TestCase');
