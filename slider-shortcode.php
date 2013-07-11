<?php
/*
* Plugin Name: Slider Shortcode
* Plugin URI:
* Description: A [slider] shortcode plugin.
* Version: 0.1
* Author: Amir Entezaralmahdi | UBC Arts ISIT
* Author URI:http://isit.arts.ubc.ca
*
*
* This program is free software; you can redistribute it and/or modify it under the terms of the GNU
* General Public License as published by the Free Software Foundation; either version 2 of the License,
* or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
* even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*
* You should have received a copy of the GNU General Public License along with this program; if not, write
* to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

Class UBC_Slider {

    static $counter; // this is used to that the shortcodes can be placed into multipe places at the same time
    static $slider_attr;
    static $add_script;
    static $slider_options;
    static $page_layout;

    /**
     * init function.
     *
     * @access public
     * @return void
     */
    function init() {

        add_action( 'init', array(__CLASS__, 'register_scripts' ) );
        add_action( 'wp_footer', array(__CLASS__, 'print_script' ) );

		add_shortcode( 'slider', array(__CLASS__, 'shortcode' ) );
    }

    /**
     * register_scripts function.
     *
     * @access public
     * @return void
     */
    function register_scripts() {
    	self::$add_script = false;
		// register the spotlight functions
        if( !is_admin() ):
        	wp_register_script( 'ubc-slider',  plugins_url('slider-shortcode') .'/js/flexslider.min.js', array( 'jquery' ), '2.1', true );
        	wp_enqueue_style('ubc-slider',  plugins_url('slider-shortcode') .'/css/flexslider.css');
        endif;

	}

	/**
	 * print_script function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static function print_script() {
		if ( ! self::$add_script )
			return;

		wp_localize_script(
                            'ubc-slider' ,
                            'slider_options',
                            self::$slider_options
                         );
		wp_print_scripts( 'ubc-slider' );
	}


	/**
     * default_slider_options function.
     * Helper function to produce the Label and the Value for the layout options
     * @access public
     * @return void
     */
    function default_slider_options(){

        return array( 'UBC CLF Standard Spotlight' => 'standard',
                      'Blank Sliding'              => 'blank_spotlight',
                      'Multi post Sliding '        => 'multi',
                      'Transparent Slider' 	   => 'transparent',
                      'Basic Sliding' 	   => 'basic-sliding',);
    }

   /**
	 * default_slider_number_of_slides function.
	 * @access public
	 * @return void
	 */
	function default_slider_number_of_slides(){

		$default_number_of_slides = array();

		$max_number_of_slides = 11;

		for($i=1; $i < $max_number_of_slides; $i++){
		    $default_number_of_slides[$i] = array(
		        'value' => $i,
		        'label' => __( $i, 'ubc-clf' )
		    );
		}
		return $default_number_of_slides;
	}



	/**
	 * Slider Code Start
	 *
	 **/



    /**
     * clf_base_spotlight_defaults function.
     *
     * @access public
     * @return void
     */
    function defaults() {

            $spotlight_atts = array(
	            'width'             => '',
	            'height'            => '330',
	            'category'          => 0,
	            'lookandfeel'       => 'standard',
	            'maxslides'         => 1,
	            'timeout'           => null,
	            'speed'             => null,
	            'effect'            => null,
                'slider_margin'     => 'false',
	            'read_more_text'    => 'Read more',
	            'read_more_check'   => false,
	            'order_by'          => null,
	            'order'             => null,
	            'remove_link_to'    => null
            );
            return $spotlight_atts;
    }

    /**
     * shortcode function.
     *
     * @access public
     * @param mixed $attr
     * @return void
     */
    function shortcode( $attr ) {
        global $post;
        self::$page_layout =  get_post_meta( $post->ID, '_layout_value', true);

    	return self::show( $attr, false );

    }
    /**
     * show function.
     *
     * @access public
     * @param array $atts (default: array())
     * @param bool $echo (default: true)
     * @return void
     */
    function show( $atts = array(), $echo = true ) {
        self::$add_script = true;

        self::$slider_attr = shortcode_atts( UBC_Slider::defaults(), $atts );

       	$html = UBC_Slider::shell();

        self::$counter++;

        if( $echo )
        	echo $html;
        else
        	return $html;

    }

    /**
     * shell function.
     *
     * @access public
     * @return void
     */
    function shell() {

    	$html = '<div class="flexslider '.self::get_slider_class().' ">';
    	$html .= self::slider_items();
    	$html .= '</div>';
    	return $html;
    }

    /**
     * get_slider_class function.
     *
     * @access public
     * @return void
     */
    function get_slider_class(){

        $slider_class = '';
    	switch( self::$slider_attr['lookandfeel'] ) {

    		case 'standard':
    			$slider_class = 'ubc-carousel standard';
    			break;

    		case 'blank':
    			$slider_class = 'ubc-carousel blank_spotlight';
    			break;

    		case 'multi':
    			$slider_class = 'ubc-carousel multi';
    			break;

                case 'transparent':
    			$slider_class = 'ubc-carousel transparent';
    			break;

                case 'basic-sliding':
    			$slider_class = 'ubc-carousel basic-sliding';
    			break;
    		default:
    			$slider_class =  self::$slider_attr['lookandfeel'];
    			break;
    	}

        // if admin has selected to remove the margin on slider, add a class
        if(self::$slider_attr['slider_margin'] == 'true'){
            $slider_class .= ' expanded-slider';
        }
        return $slider_class;
    }

    /**
     * get_slider_image function.
     *
     * @access public
     * @return void
     */
    function get_slider_image( $size = null) {
        global $post;
        
        if( !$size )
        $size = UBC_Slider::get_template_slider_size();
        //1. Try the Featured image in the post
    	if ( has_post_thumbnail() ){

            if(function_exists('wave_resize_featured_image')){
            	$html .= wave_resize_featured_image($post->ID, $size['width'], $size['height']);
			} else {
                $html .= get_the_post_thumbnail();
            }
        } else {

            $html .= '<img src="http://placehold.it/'.$size['width'].'x'.$size['height'].'" alt="Image Placeholder">';
        }

        return $html;

    }

     /**
     * get_template_slider_size.
     *
     * @access public
     * @return void
     */
    function get_template_slider_size() {
        if(!empty(self::$page_layout))
            $current_layout = self::$page_layout;
        else if (class_exists(UBC_Collab_Theme_Options))
            $current_layout = UBC_Collab_Theme_Options::get('layout');
        
        $image_size = array(
            'width'  => '',
            'height' => ''
        );
        switch ($current_layout){
            //span12
            case 'l1-column':
                $image_size['width'] = '1200';
                $image_size['height'] = ( empty( self::$slider_attr['height'] ) ? '500' : self::$slider_attr['height'] );
                break;
            //span8
            case 'l2-column-ms':
            case 'l2-column-sm':
                $image_size['width'] = '900';
                $image_size['height'] = ( empty( self::$slider_attr['height'] ) ? '350' : self::$slider_attr['height'] );
                break;
            case 'l3-column-msp':
            case 'l3-column-pms':
            case 'l3-column-psm':
                $image_size['width'] = '600';
                $image_size['height'] = ( empty( self::$slider_attr['height'] ) ? '350' : self::$slider_attr['height'] );
                break;
            default:
                $image_size['width'] = '200';
                $image_size['height'] = '100';
                break;
        }
        return $image_size;
    }

        /**
     * get_slider_image_src function.
     *
     * @access public
     * @return void
     */
    function get_slider_image_src($img) {

        return (preg_match('~\bsrc="([^"]++)"~', $img, $matches)) ? $matches[1] : '';

        return $html;

    }
    /**
     * get_slider_caption function.
     *
     * @access public
     * @return void
     */
    function get_slider_caption(){
    	$photo_caption = get_post_meta( get_the_ID(), 'photo-caption', true );

		if($photo_caption != '' )
			$html .= '<div class="photos-caption">'.$photo_caption.'</div>';

    	return $html;
    }
    /**
     * slider_controls function.
     *
     * @access public
     * @return void
     */
    function slider_controls() {
    	// slider controls are generated by JS
    	// if no slides exist there is no js
    	// if js is disabled there should be no slide controls
    }

    /**
     * slider_items function.
     *
     * @access public
     * @return void
     */
    function slider_items() {

    	$query_attr = array();
    	if( !in_array( self::$slider_attr['category'], array( 0, 'all', '0') ) ){
    		$query_attr['cat'] 		= self::$slider_attr['category'];
        }

    	$query_attr['posts_per_page'] = self::$slider_attr['maxslides'];

		$spotlight_query = new WP_Query( $query_attr);

    	$html = '<ul class="slides">';

		while ( $spotlight_query->have_posts() ): $spotlight_query->the_post();

                    switch( self::$slider_attr['lookandfeel'] ) {

                        /* HTML code for Multi Slider*/
                        case 'multi':
                            self::$slider_options = array(  'animation'  => 'slide',
                                                            'controlNav' => 'thumbnails', );

                                $html .=  '<li data-thumb="'.self::get_slider_image_src(self::get_slider_image(array( 'width'=>193, 'height'=>86) )).'">';
                                $html .=       '<a class="slider-image" href="'.get_permalink().'" title="'.get_the_title().'">';
                                $html .=            self::get_slider_image();
                                $html .=       '</a>';
                                $html .= '</li>';
                            break;

                        /* HTML code for Basic Slider*/
                        case 'basic-sliding':
                            self::$slider_options = array( 'pausePlay' => '',
                                                           'controlNav' => 1,
                                                           'start' => 'start_slider_counter',
                                                           'after' => 'after_slider_counter',);


                            $html .= '<li class="flex-slide"><a class="slider-image" href="'.get_permalink().'" title="'.get_the_title().'">';

                                $html .= self::get_slider_image();

                                $html .= '</a>';
                                $html .= self::get_slider_caption();

                                $html .= '<div class="carousel-caption">';
                                $html .= '<h4><a href="'.get_permalink().'"> '.get_the_title().'</a></h4>';
                                $html .= '<p>'.get_the_excerpt();
                                $read_more = ( empty( self::$slider_attr['read_more_text'] ) ? 'Read More' : self::$slider_attr['read_more_text'] );

                                if( self::$slider_attr['read_more_check'] )
                                        $html .= ' <a href="'.get_permalink().'" title="Read More">'.$read_more.'</a></p>';

                                $html .= '</div>';
                                $html .= '</li>';
                            break;

                        /* HTML code for standard, blank, and transparent Slider*/
                        case 'standard':
                        case 'blank':
                        case 'transparent':
                        default:
                            self::$slider_options = array( 'pausePlay' => 1,
                                                            'controlNav' => '',
                                                             'start' => 'start_slider_counter',
                                                             'after' => 'after_slider_counter',);


                            $html .= '<li class="flex-slide"><a class="slider-image" href="'.get_permalink().'" title="'.get_the_title().'">';

                                $html .= self::get_slider_image();

                                $html .= '</a>';
                                $html .= self::get_slider_caption();

                                $html .= '<div class="carousel-caption">';
                                $html .= '<h4><a href="'.get_permalink().'"> '.get_the_title().'</a></h4>';
                                $html .= '<p>'.get_the_excerpt();
                                $read_more = ( empty( self::$slider_attr['read_more_text'] ) ? 'Read More' : self::$slider_attr['read_more_text'] );

                                if( self::$slider_attr['read_more_check'] )
                                        $html .= ' <a href="'.get_permalink().'" title="Read More">'.$read_more.'</a></p>';

                                $html .= '</div>';
                                $html .= '</li>';
                            break;

                            }

		endwhile;
	 $html .= '</ul>';

	 return $html;
    }

}

UBC_Slider::init();