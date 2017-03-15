<?php if ( ! defined( 'SLZ' ) ) {
	die( 'Forbidden' );
}

/**
 * Background Image
 */
class SLZ_Option_Type_Background_Image extends SLZ_Option_Type {

	public function get_type() {
		return 'background-image';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value' => '',
			'choices' => array()
		);
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			'slz-option-' . $this->get_type(),
			slz_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/css/styles.css'),
			array(),
			slz()->manifest->get_version()
		);
		wp_enqueue_script(
			'slz-option-' . $this->get_type(),
			slz_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/scripts.js'),
			array( 'jquery', 'slz-events' ),
			slz()->manifest->get_version(),
			true
		);

		/*
		 * ensures that the static of option type upload
		 * and image-picker is enqueued
		 */
		slz()->backend->enqueue_options_static(array(
			'background-image-dummy-upload' => array(
				'type' => 'upload'
			),
			'background-image-dummy-image-picker' => array(
				'type' => 'image-picker'
			),
		));
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		$option = $this->check_parameters( $option );
		$data   = $this->check_data( $option, $data );

		return slz_render_view( slz_get_framework_directory('/includes/option-types/' . $this->get_type() . '/view.php'), array(
			'id'     => $id,
			'option' => $option,
			'data'   => $data
		) );
	}

	private function check_parameters( $option ) {

		if ( empty( $option['choices'] ) || ! is_array( $option['choices'] ) ) {
			$option['choices'] = array();
		}
		if ( empty( $option['value'] ) || ! in_array( $option['value'], array_keys( $option['choices'] ) ) ) {
			$option['value'] = '';
		}

		return $option;
	}

	private function check_data( $option, $data ) {

		$value = ( ! empty( $option['value'] ) ) ? $option['value'] : '';
		unset( $option['value'] );

		$data['value'] = array_merge(
			array(
				'type'       => ( ! empty( $value ) ) ? 'predefined' : 'custom',
				'predefined' => $value,
				'custom'     => '',
				'data'       => ( ! empty( $option['choices'][ $value ]['css'] ) ) ? $option['choices'][ $value ]['css'] : array()
			),
			(array) $data['value']
		);

		return $data;
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value )
	{
		if (is_array($input_value)) {
			if ( empty( $input_value['type'] ) ) {
				$input_value['type'] = ( ! empty( $option['choices'] ) ) ? 'predefined' : 'custom';
			}

			if ( $input_value['type'] === 'custom') {
				if( $attachment_id = intval($input_value['custom']) ) {
					$input_value['predefined'] = $option['value'];
					$attachment_url = wp_get_attachment_url($attachment_id);
					$input_value['data']       = array(
						'icon' => $attachment_url,
						'css'  => array(
							'background-image' =>  'url("' . $attachment_url . '")'
						)
					);
				} else {
					$input_value['predefined'] = $option['value'];
					$input_value['data']       = array(
						'icon' => '',
						'css'  => array()
					);
				}

			} else {
				$input_value['predefined'] = ( isset( $input_value['predefined'] ) ) ? $input_value['predefined'] : $option['value'];
				$input_value['custom']     = '';
				$input_value['data']       = ( ! empty( $option['choices'][ $input_value['predefined'] ] ) ) ? $option['choices'][ $input_value['predefined'] ] : array();
			}
		} else {
			$input_value = $option['value'];
		}

		return $input_value;
	}
}

SLZ_Option_Type::register('SLZ_Option_Type_Background_Image');