<?php

namespace Codelight\MediaFromProduction;

class MediaFromProduction
{

    /**
     * Production URL
     *
     * @since 1.0.0
     * @var string
     */
    public $productionUrl = '';

    /**
     * Holds list of upload directories
     * Can set manually here, or allow function below to automatically create it
     *
     * @since 1.0.0
     * @var array
     */
    public $directories = [];

    /**
     * Start Month
     *
     * @since 1.0.0
     * @var int
     */
    public $start_month = false;

    /**
     * Start Year
     *
     * @since 1.0.0
     * @var int
     */
    public $start_year = false;

    /**
     * Primary constructor.
     *
     * @since 1.0.0
     */
    function __construct()
    {

        // Set upload directories
        add_action('init', [$this, 'set_upload_directories']);

        // Update Image URLs
        add_filter('wp_calculate_image_srcset', [$this, 'changeSrcSetUrls'], 10, 5);
        add_filter('wp_get_attachment_image_src', [$this, 'image_src']);
        add_filter('wp_get_attachment_image_attributes', [$this, 'image_attr'], 99);
        add_filter('wp_prepare_attachment_for_js', [$this, 'image_js'], 10, 3);
        add_filter('the_content', [$this, 'image_content']);

    }

    public function changeSrcSetUrls($sources, $sizeArray, $imageSrc, $imageMeta, $attachmentId)
    {
        if (is_array($sources)) {
            foreach ($sources as &$source) {
                $source['url'] = $this->update_image_url($source['url']);
            }
        }

        return $sources;
    }

    /**
     * Set upload directories
     *
     * @since 1.0.0
     */
    function set_upload_directories()
    {
        if (empty($this->directories)) {
            $this->directories = $this->get_upload_directories();
        }
    }

    /**
     * Determine Upload Directories
     *
     * @since 1.0.0
     */
    function get_upload_directories()
    {

        // Include all upload directories starting from a specific month and year
        $month = str_pad(apply_filters('be_media_from_production_start_month', $this->start_month), 2, 0, STR_PAD_LEFT);
        $year  = apply_filters('be_media_from_production_start_year', $this->start_year);

        $upload_dirs = [];

        if ($month && $year) {
            for ($i = 0; $year . $month <= date('Ym'); $i++) {
                $upload_dirs[] = $year . '/' . $month;
                $month++;
                if (13 == $month) {
                    $month = 1;
                    $year++;
                }
                $month = str_pad($month, 2, 0, STR_PAD_LEFT);
            }
        }

        return apply_filters('be_media_from_production_directories', $upload_dirs);

    }

    /**
     * Modify Main Image URL
     *
     * @since 1.0.0
     * @param array $image
     * @return array $image
     */
    function image_src($image)
    {

        if (isset($image[0]))
            $image[0] = $this->update_image_url($image[0]);

        return $image;

    }

    /**
     * Modify Image Attributes
     *
     * @since 1.0.0
     * @param array $attr
     * @return array $attr
     */
    function image_attr($attr)
    {

        if (isset($attr['srcset']))
            $attr['srcset'] = $this->update_image_url($attr['srcset']);

        return $attr;

    }

    /**
     * Modify Image for Javascript
     * Primarily used for media library
     *
     * @since 1.3.0
     * @param array      $response Array of prepared attachment data
     * @param int|object $attachment Attachment ID or object
     * @param array      $meta Array of attachment metadata
     * @return array     $response   Modified attachment data
     */
    function image_js($response, $attachment, $meta)
    {

        if (isset($response['url']))
            $response['url'] = $this->update_image_url($response['url']);

        foreach ($response['sizes'] as &$size) {
            $size['url'] = $this->update_image_url($size['url']);
        }

        return $response;
    }

    /**
     * Modify Images in Content
     *
     * @since 1.2.0
     * @param string $content
     * @return string $content
     */
    function image_content($content)
    {
        $upload_locations = wp_upload_dir();

        $regex = '/https?\:\/\/[^\" ]+/i';
        preg_match_all($regex, $content, $matches);

        foreach ($matches[0] as $url) {
            if (false !== strpos($url, $upload_locations['baseurl'])) {
                $new_url = $this->update_image_url($url);
                $content = str_replace($url, $new_url, $content);
            }
        }

        return $content;
    }

    /**
     * Convert a URL to a local filename
     *
     * @since 1.4.0
     * @param string $url
     * @return string $local_filename
     */
    function local_filename($url)
    {
        $upload_locations = wp_upload_dir();
        $local_filename   = str_replace($upload_locations['baseurl'], $upload_locations['basedir'], $url);

        return $local_filename;
    }

    /**
     * Determine if local image exists
     *
     * @since 1.4.0
     * @param string $url
     * @return boolean
     */
    function local_image_exists($url)
    {
        return file_exists($this->local_filename($url));
    }

    /**
     * Update Image URL
     *
     * @since 1.0.0
     * @param string $imageUrl
     * @return string $imageUrl
     */
    function update_image_url($imageUrl)
    {
        if (!$imageUrl) {
            return $imageUrl;
        }

        if ($this->local_image_exists($imageUrl)) {
            return $imageUrl;
        }

        $productionUrl = esc_url(apply_filters('be_media_from_production_url', $this->productionUrl));
        if (empty($productionUrl)) {
            return $imageUrl;
        }

        $exists      = false;
        $upload_dirs = $this->directories;
        if ($upload_dirs) {
            foreach ($upload_dirs as $option) {
                if (strpos($imageUrl, $option)) {
                    $exists = true;
                }
            }
        }

        if ($exists) {
            return $imageUrl;
        }

        $remoteFolder = apply_filters('be_media_from_production_remote_content_dir', false);

        if (false === stristr($imageUrl, site_url())) {
            // Ensure duplicate URL is not added.
            // Todo: test, this might break something in a different scenario?
            if (false === stristr($imageUrl, $productionUrl)) {
                $imageUrl = $productionUrl . $imageUrl;
            }
        } else {
            $imageUrl = str_replace(site_url(), $productionUrl, $imageUrl);
        }

        if ($remoteFolder) {
            $localContentDir = defined('CONTENT_DIR') ? CONTENT_DIR : 'wp-content';
            $imageUrl = str_replace($localContentDir, $remoteFolder, $imageUrl);
        }

        return $imageUrl;
    }

}
