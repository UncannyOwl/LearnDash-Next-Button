/**
 * Add the contents of this file to functions.php in the child theme and course-steps.php to /learndash/ld30/modules in the child theme.	
 * Filter applied for next redirect after completion of topic/lesson. Advanced the user in the course sequence.
 *
 * @param $next string
 * @param $post object
 *
 * @return string
 */
 
function custom_next_step_on_complete( $next, $post ) {
	global $post;
	$permalink = '';
	if ( ! is_singular() || empty( $post ) ) {
		return $permalink;
	}
	if ( $post->post_type == 'sfwd-topic' || $post->post_type == 'sfwd-lessons' || $post->post_type == 'sfwd-quiz' ) {
		$course_id = learndash_get_course_id( $post );
		$lessons   = learndash_get_course_lessons_list( $course_id );
		foreach ( $lessons as $lesson_instance ) {
			$lesson = $lesson_instance['post'];
			if ( $lesson instanceof WP_Post ) {
				$posts[] = $lesson;
			}
			$lesson_topics = learndash_get_topic_list( $lesson->ID, $course_id );
			if ( ! empty( $lesson_topics ) ) {
				foreach ( $lesson_topics as $lesson_topic ) {
					$posts[]       = $lesson_topic;
					$topic_quizzes = learndash_get_lesson_quiz_list( $lesson_topic->ID, get_current_user_id(), $course_id );
					if ( ! empty( $topic_quizzes ) ) {
						foreach ( $topic_quizzes as $topic_quiz ) {
							$posts[] = $topic_quiz['post'];
						}
					}
				}
			}
			$lesson_quizzes = learndash_get_lesson_quiz_list( $lesson->ID, get_current_user_id(), $course_id );
			if ( ! empty( $lesson_quizzes ) ) {
				foreach ( $lesson_quizzes as $lesson_quiz ) {
					$posts[] = $lesson_quiz['post'];
				}
			}
			
		}
		$course_quizzes = learndash_get_course_quiz_list( $course_id );
		if ( ! empty( $course_quizzes ) ) {
			foreach ( $course_quizzes as $course_quiz ) {
				$posts[] = $course_quiz['post'];
			}
		}
	}
	
	foreach ( $posts as $k => $p ) {
		if ( $p instanceof WP_Post ) {
			if ( $p->ID == $post->ID ) {
				$found_at = $k;
				break;
			}
		}
	}
	
	if ( isset( $found_at ) && ! empty( $posts[ $found_at + 1 ] ) ) {
		$permalink = get_permalink( $posts[ $found_at + 1 ]->ID );
		
		return $permalink;
		
	} else {
		return $permalink;
	}
}

add_filter( 'learndash_completion_redirect', 'custom_next_step_on_complete', 10, 2 );

/**
 * Filter applied for next redirect after completion of an LD quiz.
 * Default LearnDash behaviour takes the user back to parent lesson with the next button.
 *
 * @param $return_link
 * @param $url
 *
 * @return string
 */
function learndash_quiz_continue_link_custom( $return_link, $url ) {
	global $status, $pageQuizzes, $post;
	$id = $post->ID;
	
	$course_id = learndash_get_course_id( $id );
	if ( ( !empty( $course_id ) ) && ( LearnDash_Settings_Section::get_section_setting('LearnDash_Settings_Courses_Builder', 'shared_steps' ) == 'yes' ) ) {
		$lesson_id = learndash_course_get_single_parent_step( $course_id, $id );
		if ( empty( $lesson_id ) ) {
			$url = get_permalink( $course_id );
			$url = custom_next_step_on_complete( $url, $post);
			$url = add_query_arg(
				array(
					'quiz_type' 	=> 'global',
					'quiz_redirect' => 0,
					'course_id'		=> $course_id,
					'quiz_id'		=> $id
				),
				$url
			);
			
		} else {
			$url = get_permalink( $lesson_id );
			$url = custom_next_step_on_complete( $url, $post);
			$url = add_query_arg(
				array(
					'quiz_type' 	=> 'lesson',
					'quiz_redirect' => 0,
					'lesson_id'		=> $lesson_id,
					'quiz_id'		=> $id
				),
				$url
			);
		}
		
		if ( ( isset( $url ) ) && ( !empty( $url ) ) ) {
			$returnLink = '<a id="quiz_continue_link" href="'. $url .'">' . esc_html( LearnDash_Custom_Label::get_label( 'button_click_here_to_continue' ) ) . '</a>';
		}
	} else {
		$quizmeta = get_post_meta( $id, '_sfwd-quiz' , true );
		
		if ( ! empty( $quizmeta['sfwd-quiz_lesson'] ) ) {
			$return_id = $quiz_lesson = $quizmeta['sfwd-quiz_lesson'];
		}
		
		if ( empty( $quiz_lesson) ) {
			$return_id = $course_id = learndash_get_course_id( $id );
			$url = get_permalink( $return_id );
			$url = custom_next_step_on_complete( $url, $post);
			$url .= strpos( 'a'.$url, '?' )? '&':'?';
			$url .= 'quiz_type=global&quiz_redirect=0&course_id='.$course_id.'&quiz_id='.$id;
			$returnLink = '<a id="quiz_continue_link" href="'.$url.'">' . esc_html( LearnDash_Custom_Label::get_label( 'button_click_here_to_continue' ) ) . '</a>';
		} else	{
			$url = get_permalink( $return_id );
			$url = custom_next_step_on_complete( $url, $post);
			$url .= strpos( 'a'.$url, '?' )? '&':'?';
			$url .= 'quiz_type=lesson&quiz_redirect=0&lesson_id='.$return_id.'&quiz_id='.$id;
			$returnLink = '<a id="quiz_continue_link" href="'.$url.'">' . esc_html( LearnDash_Custom_Label::get_label( 'button_click_here_to_continue' ) ) . '</a>';
		}
	}
	
	return $returnLink;
}

add_filter( 'learndash_quiz_continue_link', 'learndash_quiz_continue_link_custom', 10, 2 );
