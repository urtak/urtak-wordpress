<?php

if(!class_exists('WordPressUrtak') && class_exists('Urtak')) {
	class WordPressUrtak extends Urtak {

		protected function make_request($path, $method, $data = array()) {
			$request_args = array();
			$request_args['headers'] = array();
			$request_args['headers']['Accept'] = $this->media_types();
			$request_args['method'] = $method;
			$request_args['redirection'] = 0;
			$request_args['sslverify'] = false;
			$request_args['user-agent'] = $this->client_name;

			$signed_data = array_filter(array_merge($data, $this->create_signature()));

			$url = $this->api_home . $path;

			if('GET' === $method) {
				$url = add_query_arg(urlencode_deep($signed_data), $url);
			} else if('POST' === $method || 'PUT' === $method) {
				$json = json_encode($signed_data);

				$request_args['body'] = $json;
				$request_args['headers']['Content-Type'] = 'application/json';
			}

			$response = wp_remote_request($url, $request_args);
			if(is_wp_error($response)) {
				return new UrtakResponse('', 500, 'JSON');
			} else if(in_array($this->api_format, array('JSON', 'XML'))) {
				return new UrtakResponse(wp_remote_retrieve_body($response), wp_remote_retrieve_response_code($response), $this->api_format);
			}
		}
	}
}