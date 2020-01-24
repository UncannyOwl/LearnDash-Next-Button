<?php
/**
 * Displays a Course Prev/Next navigation.
 *
 * Available Variables:
 *
 * $course_id        : (int) ID of Course
 * $course_step_post : (int) ID of the lesson/topic post
 * $user_id          : (int) ID of User
 * $course_settings  : (array) Settings specific to current course
 * $can_complete     : (bool) Can the user mark this lesson/topic complete?
 *
 * @since 3.0
 *
 * @package LearnDash
 */

function learndash_previous_post_link_custom( $post ) {
	global $post;
	$permalink = '';
	if ( ! is_singular() || empty( $post) ) {
		return $permalink;
	}
	if ( $post->post_type == 'sfwd-topic' || $post->post_type == 'sfwd-lessons' || $post->post_type == 'sfwd-quiz'  ) {
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
			foreach( $course_quizzes as $course_quiz ) {
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
	
	if ( isset( $found_at) && ! empty( $posts[ $found_at -1] ) ) {
		$permalink = get_permalink( $posts[ $found_at -1]->ID );
		
		return $permalink;
		
	} else {
		return $permalink;
	}
}
function learndash_next_post_link_custom( $post ) {
	global $post;
	$permalink = '';
	if ( ! is_singular() || empty( $post) ) {
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
			foreach( $course_quizzes as $course_quiz ) {
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
	
	if ( isset( $found_at) && ! empty( $posts[ $found_at +1] ) ) {
		$permalink = get_permalink( $posts[ $found_at +1]->ID );
		
		return $permalink;
		
	} else {
		return $permalink;
	}
}
$learndash_previous_nav = learndash_previous_post_link_custom( $course_step_post );
$learndash_next_nav     = '';
$button_class           = 'ld-button ' . ( $context == 'focus' ? 'ld-button-transparent' : '' );

/*
 * See details for filter 'learndash_show_next_link' https://bitbucket.org/snippets/learndash/5oAEX
 *
 * @since version 2.3
 */

$current_complete = false;

if ( ( isset( $course_settings['course_disable_lesson_progression'] ) ) && ( $course_settings['course_disable_lesson_progression'] === 'on' ) ) {
	$current_complete = true;
} else {
	
	if ( $course_step_post->post_type == 'sfwd-topic' ) {
		$current_complete = learndash_is_topic_complete( $user_id, $course_step_post->ID );
	} elseif ( $course_step_post->post_type == 'sfwd-lessons' ) {
		$current_complete = learndash_is_lesson_complete( $user_id, $course_step_post->ID );
	}
	
	if ( ( $current_complete !== true ) && ( learndash_is_admin_user( $user_id ) ) ) {
		$bypass_course_limits_admin_users = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_General_Admin_User', 'bypass_course_limits_admin_users' );
		
		if ( $bypass_course_limits_admin_users == 'yes' ) {
			$current_complete = true;
		}
	}
}

if ( apply_filters( 'learndash_show_next_link', $current_complete, $user_id, $course_step_post->ID ) ) {
	
	$learndash_next_nav = learndash_next_post_link_custom( $course_step_post );
	
}

if ( $course_step_post->post_type == 'sfwd-lessons' ) {
	$learndash_next_nav = learndash_next_post_link_custom( $course_step_post );
}

$complete_button = learndash_mark_complete( $course_step_post );
if ( ! empty( $learndash_previous_nav ) || ! empty( $learndash_next_nav ) || ! empty( $complete_button ) ) : ?>

    <div class="ld-content-actions">
		
		<?php
		/**
		 * Action to add custom content before the course steps (all locations)
		 *
		 * @since 3.0
		 */
		do_action( 'learndash-all-course-steps-before', get_post_type(), $course_id, $user_id );
		do_action( 'learndash-' . $context . '-course-steps-before', get_post_type(), $course_id, $user_id );
		$learndash_current_post_type = get_post_type();
		?>
        <div class="ld-content-action<?php if ( ! $learndash_previous_nav ) : ?> ld-empty<?php endif; ?>">
			<?php if ( $learndash_previous_nav ) : ?>
                <a class="<?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_attr( $learndash_previous_nav ); ?>">
                    <span class="ld-icon ld-icon-arrow-left"></span>
                    <span class="ld-text"><?php echo __('Previous'); ?></span>
                </a>
			<?php endif; ?>
        </div>
		
		<?php
		$parent_id = ( get_post_type() == 'sfwd-lessons' ? $course_id : learndash_course_get_single_parent_step( $course_id, get_the_ID() ) );
		
		if ( $parent_id && $context != 'focus' ) :
			?>
            <a href="<?php echo esc_attr( learndash_get_step_permalink( $parent_id, $course_id ) ); ?>" class="ld-primary-color"><?php
				echo learndash_get_label_course_step_back( get_post_type( $parent_id ) );
				?></a>
		<?php endif; ?>

        <div class="ld-content-action<?php if ( ( ! $can_complete ) && ( ! $learndash_next_nav ) ) : ?> ld-empty<?php endif; ?>">
			<?php
			if ( isset( $can_complete ) && $can_complete && ! empty( $complete_button ) ) :
				echo learndash_mark_complete( $course_step_post );
            elseif ( $learndash_next_nav ) : ?>
                <a class="<?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_attr( $learndash_next_nav ); ?>">
                    <span class="ld-text"><?php echo __('Next'); ?></span>
                    <span class="ld-icon ld-icon-arrow-right"></span>
                </a>
			<?php endif; ?>
        </div>
		
		<?php
		/**
		 * Action to add custom content after the course steps (all locations)
		 *
		 * @since 3.0
		 */
		do_action( 'learndash-all-course-steps-after', get_post_type(), $course_id, $user_id );
		do_action( 'learndash-' . $context . '-course-steps-after', get_post_type(), $course_id, $user_id );
		?>

    </div> <!--/.ld-topic-actions-->

<?php
endif;
