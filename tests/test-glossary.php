<?php

class GlossaryTest extends WP_UnitTestCase {

	public $plugin;

	function setUp() {

		parent::setUp();

		// Setup plugin options so that tooltips are enabled.
		$option_values = array(
			'posttypes'		=> array( 'post', 'page' ),
			'tooltip_style'	=> 'box',
			'excerpt_limit'	=> '60',
			'slug'			=> 'glossary',
			'slug-cat'		=> 'glossary-cat',
			'tooltip'		=> 'on',
		);
		add_option( 'glossary-settings', $option_values );
	}


	function testGlossaryAutoLink() {
		// Setup original body and expected output.
		$body = 'Post content contains the word philosophy which should have a definition.';
		$body_tooltip_box = 'Post content contains the word <span class="glossary-tooltip"><span class="glossary-tooltip-item"><a href="http://example.org/?glossary=philosophy">philosophy</a></span><span class="glossary-tooltip-content clearfix"><span class="glossary-tooltip-text">From the latin for love of wisdom.</span></span></span> which should have a definition.';

		// Create glossary term.
		$glossary_post_arr = array(
				'post_title' 	=> 'Philosophy',
				'post_content'	=> 'From the latin for love of wisdom.',
				'post_type'		=> 'glossary',
				'post_status'	=> 'publish',
		);
		$glossary_post_id = wp_insert_post( $glossary_post_arr );
		add_post_meta( $glossary_post_id, 'glossary_link_type', 'internal' );

		// Create post containing term.
		$post_arr = array(
				'post_title' 	=> 'This is a test post title',
				'post_content'	=> $body,
				'post_type'		=> 'post',
				'post_status'	=> 'publish',
		);
		$post_id = wp_insert_post( $post_arr );

		// Fake going to the post URL.
		$this->go_to( get_permalink( $post_id ) );

		// Make sure the relevant globals are set.
		global $post;
		setup_postdata( $post );

		// Initialize substitution engine.
		$engine = new Glossary_Tooltip_Engine;

		$actual = $engine->glossary_auto_link( $post_arr['post_content'] );

		$this->assertEquals( $actual, $body_tooltip_box );
	}


	function testGlossaryAutoLinkLongText() {
		// Setup original body and expected output.
		$body = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus tempus lorem purus, vitae sollicitudin libero egestas at. Morbi vestibulum mi justo, nec iaculis magna volutpat et. Integer mattis euismod pellentesque. Donec eu mi eu leo lobortis pulvinar. Praesent mattis ac est quis malesuada. Aenean aliquet urna nec justo semper efficitur. Nam convallis lacus eu quam cursus, vel convallis felis rutrum. Pellentesque lacinia elit augue. Suspendisse potenti. Suspendisse in blandit tellus, eget convallis ante. Maecenas hendrerit sit amet augue in semper. Duis ut libero ut ante dapibus accumsan.

Mauris risus elit, viverra sit amet venenatis eu, bibendum a est. In sollicitudin lacus in diam tempor pharetra. Ut porttitor ligula non ipsum ornare vulputate. Sed vehicula ut orci laoreet bibendum. Suspendisse eget rutrum nisl. Fusce placerat eu erat porta posuere. Integer sit amet vehicula ante, eu euismod leo. Fusce vel felis a ipsum vestibulum dapibus a quis felis. Pellentesque pretium diam sit amet dapibus dapibus. Nunc tristique elementum sollicitudin. Sed luctus convallis pretium. Sed volutpat venenatis pharetra.

Suspendisse rhoncus et magna ac cursus. Aenean sit amet mi elementum dui ullamcorper auctor ut id nulla. Proin vestibulum erat et justo pellentesque auctor. Morbi eget justo tortor. In id quam lorem. Proin vitae ligula dapibus, feugiat ligula vitae, vulputate quam. Nullam ipsum erat, scelerisque sit amet tempor at, sagittis nec quam. Nullam consectetur lorem diam, sit amet elementum quam venenatis in. Donec in turpis et mauris semper ornare eget molestie nisl. In vehicula libero eu enim imperdiet semper. Phasellus rutrum lacus urna, ac feugiat nibh mattis sed. Proin in lectus neque. Maecenas dictum tempor tincidunt. Donec leo libero, iaculis non auctor interdum, commodo vitae libero. Curabitur quis mauris magna.

Etiam bibendum mi quis arcu scelerisque, vehicula fringilla odio ornare. Donec imperdiet tincidunt viverra. Nunc molestie metus at accumsan sodales. Curabitur diam lorem, pharetra a purus sit amet, cursus tempor est. Vestibulum dignissim leo orci, id molestie metus sagittis nec. Maecenas ligula mi, bibendum ut velit non, iaculis porttitor dolor. Aliquam congue condimentum ipsum ac auctor. Donec in felis vitae metus vulputate cursus vitae nec lacus. Aliquam luctus diam vel purus malesuada, non suscipit magna fermentum. Fusce libero sem, tempor id cursus sed, vehicula sit amet ligula. Nulla est justo, bibendum in facilisis id, bibendum vel purus. Sed nec fermentum purus. Quisque arcu urna, luctus non interdum id, auctor ut neque. Nunc vel ex feugiat, iaculis nisl vel, bibendum ipsum. Quisque lacinia pretium dapibus.

Aenean ornare enim leo, sit amet mattis magna convallis et. Vivamus lobortis scelerisque orci non dapibus. Sed vel tellus rhoncus, pulvinar risus sit amet, aliquam neque. In finibus sapien dolor, quis gravida turpis porta fringilla. Aenean convallis felis in purus condimentum, ut rhoncus ipsum imperdiet. Fusce commodo ante dapibus, porttitor nulla ac, aliquet elit. Nunc et magna lacinia, luctus diam eu, congue eros. Duis volutpat justo in metus congue, non pretium leo fermentum. Nam condimentum gravida aliquam. Curabitur vel lectus posuere, elementum purus non, commodo augue. Maecenas facilisis commodo risus a finibus. Sed rhoncus ligula nec nisi imperdiet, ac accumsan lacus ultricies. Morbi auctor dolor mi, ut tempor justo blandit et. Integer nibh dui, egestas et sagittis ac, vestibulum quis tellus. Vivamus sit amet orci turpis.';

		$body_tooltip_box = 'Lorem <span class="glossary-tooltip"><span class="glossary-tooltip-item"><a href="http://example.org/?glossary=ipsum">ipsum</a></span><span class="glossary-tooltip-content clearfix"><span class="glossary-tooltip-text">Themself</span></span></span> dolor sit amet, consectetur adipiscing elit. Phasellus tempus lorem purus, vitae sollicitudin libero egestas at. Morbi vestibulum mi justo, nec iaculis magna volutpat et. Integer mattis euismod pellentesque. Donec eu mi eu leo lobortis pulvinar. Praesent mattis ac est quis malesuada. Aenean aliquet urna nec justo semper efficitur. Nam convallis lacus eu quam cursus, vel convallis felis rutrum. Pellentesque lacinia elit augue. Suspendisse potenti. Suspendisse in blandit tellus, eget convallis ante. Maecenas hendrerit sit amet augue in semper. Duis ut libero ut ante dapibus accumsan.

Mauris risus elit, viverra sit amet venenatis eu, bibendum a est. In sollicitudin lacus in diam tempor pharetra. Ut porttitor ligula non <span class="glossary-tooltip"><span class="glossary-tooltip-item"><a href="http://example.org/?glossary=ipsum">ipsum</a></span><span class="glossary-tooltip-content clearfix"><span class="glossary-tooltip-text">Themself</span></span></span> ornare vulputate. Sed vehicula ut orci laoreet bibendum. Suspendisse eget rutrum nisl. Fusce placerat eu erat porta posuere. Integer sit amet vehicula ante, eu euismod leo. Fusce vel felis a <span class="glossary-tooltip"><span class="glossary-tooltip-item"><a href="http://example.org/?glossary=ipsum">ipsum</a></span><span class="glossary-tooltip-content clearfix"><span class="glossary-tooltip-text">Themself</span></span></span> vestibulum dapibus a quis felis. Pellentesque pretium diam sit amet dapibus dapibus. Nunc tristique elementum sollicitudin. Sed luctus convallis pretium. Sed volutpat venenatis pharetra.

Suspendisse rhoncus et magna ac cursus. Aenean sit amet mi elementum dui ullamcorper auctor ut id nulla. Proin vestibulum erat et justo pellentesque auctor. Morbi eget justo tortor. In id quam lorem. Proin vitae ligula dapibus, feugiat ligula vitae, vulputate quam. Nullam <span class="glossary-tooltip"><span class="glossary-tooltip-item"><a href="http://example.org/?glossary=ipsum">ipsum</a></span><span class="glossary-tooltip-content clearfix"><span class="glossary-tooltip-text">Themself</span></span></span> erat, scelerisque sit amet tempor at, sagittis nec quam. Nullam consectetur lorem diam, sit amet elementum quam venenatis in. Donec in turpis et mauris semper ornare eget molestie nisl. In vehicula libero eu enim imperdiet semper. Phasellus rutrum lacus urna, ac feugiat nibh mattis sed. Proin in lectus neque. Maecenas dictum tempor tincidunt. Donec leo libero, iaculis non auctor interdum, commodo vitae libero. Curabitur quis mauris magna.

Etiam bibendum mi quis arcu scelerisque, vehicula fringilla odio ornare. Donec imperdiet tincidunt viverra. Nunc molestie metus at accumsan sodales. Curabitur diam lorem, pharetra a purus sit amet, cursus tempor est. Vestibulum dignissim leo orci, id molestie metus sagittis nec. Maecenas ligula mi, bibendum ut velit non, iaculis porttitor dolor. Aliquam congue condimentum <span class="glossary-tooltip"><span class="glossary-tooltip-item"><a href="http://example.org/?glossary=ipsum">ipsum</a></span><span class="glossary-tooltip-content clearfix"><span class="glossary-tooltip-text">Themself</span></span></span> ac auctor. Donec in felis vitae metus vulputate cursus vitae nec lacus. Aliquam luctus diam vel purus malesuada, non suscipit magna fermentum. Fusce libero sem, tempor id cursus sed, vehicula sit amet ligula. Nulla est justo, bibendum in facilisis id, bibendum vel purus. Sed nec fermentum purus. Quisque arcu urna, luctus non interdum id, auctor ut neque. Nunc vel ex feugiat, iaculis nisl vel, bibendum <span class="glossary-tooltip"><span class="glossary-tooltip-item"><a href="http://example.org/?glossary=ipsum">ipsum</a></span><span class="glossary-tooltip-content clearfix"><span class="glossary-tooltip-text">Themself</span></span></span>. Quisque lacinia pretium dapibus.

Aenean ornare enim leo, sit amet mattis magna convallis et. Vivamus lobortis scelerisque orci non dapibus. Sed vel tellus rhoncus, pulvinar risus sit amet, aliquam neque. In finibus sapien dolor, quis gravida turpis porta fringilla. Aenean convallis felis in purus condimentum, ut rhoncus <span class="glossary-tooltip"><span class="glossary-tooltip-item"><a href="http://example.org/?glossary=ipsum">ipsum</a></span><span class="glossary-tooltip-content clearfix"><span class="glossary-tooltip-text">Themself</span></span></span> imperdiet. Fusce commodo ante dapibus, porttitor nulla ac, aliquet elit. Nunc et magna lacinia, luctus diam eu, congue eros. Duis volutpat justo in metus congue, non pretium leo fermentum. Nam condimentum gravida aliquam. Curabitur vel lectus posuere, elementum purus non, commodo augue. Maecenas facilisis commodo risus a finibus. Sed rhoncus ligula nec nisi imperdiet, ac accumsan lacus ultricies. Morbi auctor dolor mi, ut tempor justo blandit et. Integer nibh dui, egestas et sagittis ac, vestibulum quis tellus. Vivamus sit amet orci turpis.';

		// Create glossary term.
		$glossary_post_arr = array(
				'post_title' 	=> 'Ipsum',
				'post_content'	=> 'Themself',
				'post_type'		=> 'glossary',
				'post_status'	=> 'publish',
		);
		$glossary_post_id = wp_insert_post( $glossary_post_arr );
		add_post_meta( $glossary_post_id, 'glossary_link_type', 'internal' );

		// Create post containing term.
		$post_arr = array(
				'post_title' 	=> 'This is a test post title',
				'post_content'	=> $body,
				'post_type'		=> 'post',
				'post_status'	=> 'publish',
		);
		$post_id = wp_insert_post( $post_arr );

		// Fake going to the post URL.
		$this->go_to( get_permalink( $post_id ) );

		// Make sure the relevant globals are set.
		global $post;
		setup_postdata( $post );

		// Initialize substitution engine.
		$engine = new Glossary_Tooltip_Engine;

		$actual = $engine->glossary_auto_link( $post_arr['post_content'] );

		$this->assertEquals( $actual, $body_tooltip_box );
	}
}

