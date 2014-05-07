<?php $runtime[1398402206] = array (
  'group' => 'e53418b99eda09e777ad4e2956b0da54',
  'description' => 'PHP 5.3.8, Environment: testing, Database: , Memcached: enabled, Testing URL: DISABLED!!!
Error reporting set to: E_ALL
Active Support (suite)',
  'total' => 0,
  'stats' => 
  array (
    'slow_methods' => 
    array (
      'test_all_caches' => 14.681180953979,
      'test_set_and_get_really_really_large_string' => 10.285933971405,
      'Test_of_pluralize_singular' => 7.0671908855438,
      'test_set_and_get_really_large_string' => 5.0768790245056,
      'Test_of_pluralize_plurals' => 4.657881975174,
      'test_init_with_wrong_server' => 4.6238989830017,
      'test_init_with_wrong_server_using_AkCache_init' => 4.5789289474487,
      'test_init_with_wrong_server_using_AkCache_lookupStore' => 4.5603761672974,
      'Test_of_singularize_plural' => 1.7712438106537,
      'test_should_detect_plurals' => 1.3758149147034,
    ),
    'memory_hungry_methods' => 
    array (
      'test_all_caches' => 3038808,
      'test_html_to_text' => 613136,
      'test_install_methods' => 493888,
      'test_init_with_wrong_server' => 352272,
      'test_remove_repositories_config' => 210880,
      'test_should_increase_memory' => 122968,
      'test_constructor_default' => 105848,
      'test_spanish_dictionary' => 57064,
      'test_mime_type_detection' => 51560,
      'Test_of_pluralize_plurals' => 50992,
    ),
    'slow_cases' => 
    array (
      'Memcached_TestCase' => 30.039716959,
      'Inflector_TestCase' => 17.031491994858,
      'Cache_TestCase' => 14.682134866714,
      'FileHandling_TestCase' => 6.2798578739166,
      'Ak_TestCase' => 1.5469310283661,
      'Conversions_TestCase' => 1.1899290084839,
      'Number_TestCase' => 1.1647770404816,
      'Config_TestCase' => 1.0291121006012,
      'PluginInstaller_TestCase' => 0.64255905151367,
      'LazyObject_TestCase' => 0.50934505462646,
    ),
    'memory_hungry_cases' => 
    array (
      'Cache_TestCase' => 3039664,
      'Conversions_TestCase' => 622800,
      'PluginInstaller_TestCase' => 495496,
      'Memcached_TestCase' => 379624,
      'Inflector_TestCase' => 150432,
      'PHP_Bug_33595_TestCase' => 124064,
      'Number_TestCase' => 111896,
      'FileHandling_TestCase' => 56080,
      'LazyObject_TestCase' => 40568,
      'Config_TestCase' => 17248,
    ),
  ),
  'cases' => 
  array (
    'Ak_TestCase' => 
    array (
      0 => 1.5469310283661,
      1 => -1944,
    ),
    'Cache_TestCase' => 
    array (
      0 => 14.682134866714,
      1 => 3039664,
    ),
    'Config_TestCase' => 
    array (
      0 => 1.0291121006012,
      1 => 17248,
    ),
    'Conversions_TestCase' => 
    array (
      0 => 1.1899290084839,
      1 => 622800,
    ),
    'FileHandling_TestCase' => 
    array (
      0 => 6.2798578739166,
      1 => 56080,
    ),
    'FileHandlingOverFtp_TestCase' => 
    array (
      0 => 0.2056097984314,
      1 => 3136,
    ),
    'StaticFuntionsForFileHandlingOverFtp_TestCase' => 
    array (
      0 => 0.40276885032654,
      1 => 2880,
    ),
    'Inflector_TestCase' => 
    array (
      0 => 17.031491994858,
      1 => 150432,
    ),
    'LazyObject_TestCase' => 
    array (
      0 => 0.50934505462646,
      1 => 40568,
    ),
    'Memcached_TestCase' => 
    array (
      0 => 30.039716959,
      1 => 379624,
    ),
    'Number_TestCase' => 
    array (
      0 => 1.1647770404816,
      1 => 111896,
    ),
    'ObjectInspection_TestCase' => 
    array (
      0 => 0.16231989860535,
      1 => 3752,
    ),
    'PHP_Bug_33595_TestCase' => 
    array (
      0 => 0.029390096664429,
      1 => 124064,
    ),
    'PluginInstaller_TestCase' => 
    array (
      0 => 0.64255905151367,
      1 => 495496,
    ),
  ),
  'methods' => 
  array (
    'test_should_get_the_right_temp_dir' => 
    array (
      0 => 0.328537940979,
      1 => 1760,
    ),
    'test_static_var_set_value_null' => 
    array (
      0 => 0.032596826553345,
      1 => 64,
    ),
    'test_static_var_set_value_true' => 
    array (
      0 => 0.06875205039978,
      1 => 152,
    ),
    'test_static_var_set_value_false' => 
    array (
      0 => 0.14029693603516,
      1 => 64,
    ),
    'test_static_var_set_value_array' => 
    array (
      0 => 0.47798681259155,
      1 => 776,
    ),
    'test_static_var_set_value_float' => 
    array (
      0 => 0.076968908309937,
      1 => -104,
    ),
    'test_static_var_set_value_object_referenced' => 
    array (
      0 => 0.13826608657837,
      1 => 256,
    ),
    'test_static_var_destruct_single_var' => 
    array (
      0 => 0.056537866592407,
      1 => -216,
    ),
    'test_static_var_destruct_all_vars' => 
    array (
      0 => 0.19851303100586,
      1 => -7496,
    ),
    'test_all_caches' => 
    array (
      0 => 14.681180953979,
      1 => 3038808,
    ),
    'test_generate_cache_filename' => 
    array (
      0 => 0.041660070419312,
      1 => 1960,
    ),
    'test_write_cache' => 
    array (
      0 => 0.081204891204834,
      1 => 1128,
    ),
    'test_read_cache' => 
    array (
      0 => 0.08234977722168,
      1 => 64,
    ),
    'test_read_config' => 
    array (
      0 => 0.26367497444153,
      1 => 5088,
    ),
    'test_parse_setting_constant' => 
    array (
      0 => 0.040557861328125,
      1 => 3296,
    ),
    'test_get_with_and_without_cache' => 
    array (
      0 => 0.43014287948608,
      1 => 3104,
    ),
    'test_should_return_null_on_unexisting_options' => 
    array (
      0 => 0.020519018173218,
      1 => 64,
    ),
    'test_should_get_default_option' => 
    array (
      0 => 0.026082038879395,
      1 => 64,
    ),
    'test_should_get_not_get_default_option_if_already_set' => 
    array (
      0 => 0.034484148025513,
      1 => 176,
    ),
    'test_html_to_text' => 
    array (
      0 => 0.15411782264709,
      1 => 613136,
    ),
    'test_html_to_text_with_entities' => 
    array (
      0 => 0.09456205368042,
      1 => 1584,
    ),
    'test_html_to_text_custom_tags' => 
    array (
      0 => 0.28033089637756,
      1 => 1584,
    ),
    'test_html_to_text_removing_js' => 
    array (
      0 => 0.25455212593079,
      1 => 1584,
    ),
    'test_html_to_with_text_using_quotes' => 
    array (
      0 => 0.15006899833679,
      1 => 1584,
    ),
    'test_html_to_text_using_smartipants' => 
    array (
      0 => 0.21756196022034,
      1 => 1584,
    ),
    'Test_file_put_contents' => 
    array (
      0 => 1.0413279533386,
      1 => 1464,
    ),
    'Test_file_get_contents' => 
    array (
      0 => 0.73784899711609,
      1 => 64,
    ),
    'Test_copy_files' => 
    array (
      0 => 0.5650749206543,
      1 => 32,
    ),
    'Test_copy_directories' => 
    array (
      0 => 0.37572908401489,
      1 => 128,
    ),
    'Test_file_delete' => 
    array (
      0 => 0.75359797477722,
      1 => 0,
    ),
    'Test_directory_delete' => 
    array (
      0 => 0.9181501865387,
      1 => 64,
    ),
    'test_mime_type_detection' => 
    array (
      0 => 0.16413307189941,
      1 => 51560,
    ),
    'test_should_read_files_using_scoped_file_get_contents_function' => 
    array (
      0 => 0.12705492973328,
      1 => 64,
    ),
    'test_dir_should_not_recurse_when_set_to_false' => 
    array (
      0 => 0.60145592689514,
      1 => -104,
    ),
    'test_should_delete_nested_directories_when_include_hidden_files' => 
    array (
      0 => 0.77882695198059,
      1 => 24,
    ),
    'test_should_create_base_path_ticket_148' => 
    array (
      0 => 0.096698999404907,
      1 => 64,
    ),
    'test_file_put_contents' => 
    array (
      0 => 0.0016219615936279,
      1 => 64,
    ),
    'test_file_get_contents' => 
    array (
      0 => 0.030403852462769,
      1 => 64,
    ),
    'test_file_delete' => 
    array (
      0 => 0.036128044128418,
      1 => 64,
    ),
    'test_directory_delete' => 
    array (
      0 => 0.032402992248535,
      1 => 64,
    ),
    'test_connect' => 
    array (
      0 => 0.010264873504639,
      1 => 1552,
    ),
    'test_disconnect' => 
    array (
      0 => 0.0048098564147949,
      1 => 64,
    ),
    'test_make_dir' => 
    array (
      0 => 0.027205944061279,
      1 => 64,
    ),
    'test_delete' => 
    array (
      0 => 0.02726411819458,
      1 => 64,
    ),
    'test_is_dir' => 
    array (
      0 => 0.034281969070435,
      1 => 64,
    ),
    'Test_of_pluralize_plurals' => 
    array (
      0 => 4.657881975174,
      1 => 50992,
    ),
    'Test_of_pluralize_singular' => 
    array (
      0 => 7.0671908855438,
      1 => 16048,
    ),
    'Test_of_singularize_plural' => 
    array (
      0 => 1.7712438106537,
      1 => 16592,
    ),
    'Test_of_titleize' => 
    array (
      0 => 0.076591014862061,
      1 => 936,
    ),
    'Test_of_camelize' => 
    array (
      0 => 0.040531873703003,
      1 => 552,
    ),
    'Test_of_underscore' => 
    array (
      0 => 0.078151941299438,
      1 => 1120,
    ),
    'Test_of_foreignKey' => 
    array (
      0 => 0.0261070728302,
      1 => 168,
    ),
    'Test_of_tableize' => 
    array (
      0 => 0.019994974136353,
      1 => 448,
    ),
    'Test_of_classify' => 
    array (
      0 => 0.023844003677368,
      1 => 448,
    ),
    'Test_of_humanize' => 
    array (
      0 => 0.036673069000244,
      1 => 64,
    ),
    'Test_of_ordinalize' => 
    array (
      0 => 0.31869721412659,
      1 => 64,
    ),
    'Test_of_unnaccent' => 
    array (
      0 => 0.0099399089813232,
      1 => 64,
    ),
    'Test_for_setting_custom_plurals' => 
    array (
      0 => 0.011313915252686,
      1 => 176,
    ),
    'Test_for_setting_custom_singulars' => 
    array (
      0 => 0.011885166168213,
      1 => 168,
    ),
    'test_should_detect_singulars' => 
    array (
      0 => 0.8609299659729,
      1 => 64,
    ),
    'test_should_detect_plurals' => 
    array (
      0 => 1.3758149147034,
      1 => 64,
    ),
    'test_should_demodulize' => 
    array (
      0 => 0.051839113235474,
      1 => 64,
    ),
    'test_should_get_controller_file_name' => 
    array (
      0 => 0.037207126617432,
      1 => 400,
    ),
    'test_singularize_singular' => 
    array (
      0 => 0.011746168136597,
      1 => 168,
    ),
    'test_simple_tableize' => 
    array (
      0 => 0.024294137954712,
      1 => 392,
    ),
    'test_spanish_dictionary' => 
    array (
      0 => 0.49471020698547,
      1 => 57064,
    ),
    'test_should_extend_a_class_given_its_name' => 
    array (
      0 => 0.010845184326172,
      1 => 2704,
    ),
    'test_should_remove_extensions_giving_its_name' => 
    array (
      0 => 0.022480964660645,
      1 => 416,
    ),
    'test_should_be_extended_using_implicit_methods' => 
    array (
      0 => 0.070810079574585,
      1 => 4224,
    ),
    'test_should_report_error_if_unregistered_methods_are_called' => 
    array (
      0 => 0.026307821273804,
      1 => 552,
    ),
    'test_should_be_extended_using_instance' => 
    array (
      0 => 0.049041986465454,
      1 => 3240,
    ),
    'test_should_allow_using_proxy_attributes_if_set_implicitly_only' => 
    array (
      0 => 0.038667917251587,
      1 => 3664,
    ),
    'test_should_allow_using_proxy_attributes_when_using_instance' => 
    array (
      0 => 0.022041082382202,
      1 => 3528,
    ),
    'test_should_respect_attribute_visibility' => 
    array (
      0 => 0.064672946929932,
      1 => 3304,
    ),
    'test_should_add_methods_by_pattern' => 
    array (
      0 => 0.051813125610352,
      1 => 3448,
    ),
    'test_should_not_allow_extending_by_class_using_by_name' => 
    array (
      0 => 0.028408050537109,
      1 => 64,
    ),
    'test_should_not_register_twice_unless_forced' => 
    array (
      0 => 0.032502174377441,
      1 => 3272,
    ),
    'test_should_return_instance_being_extended_by_name' => 
    array (
      0 => 0.013998031616211,
      1 => 3024,
    ),
    'test_should_return_instance_being_extended' => 
    array (
      0 => 0.02764105796814,
      1 => 3024,
    ),
    'test_should_report_if_lazy_objects_are_now_active' => 
    array (
      0 => 0.03453803062439,
      1 => 3024,
    ),
    'test_init_without_server_fallback_to_default' => 
    array (
      0 => 0.15298509597778,
      1 => 24016,
    ),
    'test_init_with_wrong_server' => 
    array (
      0 => 4.6238989830017,
      1 => 352272,
    ),
    'test_init_with_wrong_server_using_AkCache_init' => 
    array (
      0 => 4.5789289474487,
      1 => -1400,
    ),
    'test_init_with_wrong_server_using_AkCache_lookupStore' => 
    array (
      0 => 4.5603761672974,
      1 => 1832,
    ),
    'test_set_and_get_string' => 
    array (
      0 => 0.070189952850342,
      1 => -1448,
    ),
    'test_set_and_get_integer' => 
    array (
      0 => 0.067718029022217,
      1 => 64,
    ),
    'test_set_and_get_float' => 
    array (
      0 => 0.058856964111328,
      1 => 64,
    ),
    'test_set_and_get_array' => 
    array (
      0 => 0.064221858978271,
      1 => 64,
    ),
    'test_set_and_get_object' => 
    array (
      0 => 0.058981895446777,
      1 => 64,
    ),
    'test_set_and_get_objects_within_arrays' => 
    array (
      0 => 0.073972940444946,
      1 => 64,
    ),
    'test_set_and_get_large_strings' => 
    array (
      0 => 0.089270830154419,
      1 => 64,
    ),
    'test_set_and_get_binary_data' => 
    array (
      0 => 0.1045298576355,
      1 => 64,
    ),
    'test_set_and_get_really_large_string' => 
    array (
      0 => 5.0768790245056,
      1 => 64,
    ),
    'test_set_and_get_really_really_large_string' => 
    array (
      0 => 10.285933971405,
      1 => 152,
    ),
    'test_set_and_remove_key' => 
    array (
      0 => 0.062072992324829,
      1 => -24,
    ),
    'test_flush_group' => 
    array (
      0 => 0.095186948776245,
      1 => 296,
    ),
    'test_constructor_default' => 
    array (
      0 => 0.065566062927246,
      1 => 105848,
    ),
    'test_constructor_magic_string' => 
    array (
      0 => 0.019191026687622,
      1 => 64,
    ),
    'test_time_units' => 
    array (
      0 => 0.064504146575928,
      1 => 64,
    ),
    'test_byte_units' => 
    array (
      0 => 0.046082019805908,
      1 => 64,
    ),
    'test_years_from_now' => 
    array (
      0 => 0.045057058334351,
      1 => 64,
    ),
    'test_years_ago' => 
    array (
      0 => 0.042710065841675,
      1 => 64,
    ),
    'test_months_from_now' => 
    array (
      0 => 0.049988031387329,
      1 => 64,
    ),
    'test_months_ago' => 
    array (
      0 => 0.045082092285156,
      1 => 64,
    ),
    'test_weeks_from_now' => 
    array (
      0 => 0.048070907592773,
      1 => 64,
    ),
    'test_weeks_ago' => 
    array (
      0 => 0.045572996139526,
      1 => 64,
    ),
    'test_days_from_now' => 
    array (
      0 => 0.045520067214966,
      1 => 64,
    ),
    'test_days_ago' => 
    array (
      0 => 0.047727108001709,
      1 => 64,
    ),
    'test_hours_from_now' => 
    array (
      0 => 0.066499948501587,
      1 => 64,
    ),
    'test_hours_ago' => 
    array (
      0 => 0.07166600227356,
      1 => 64,
    ),
    'test_minutes_from_now' => 
    array (
      0 => 0.055095911026001,
      1 => 64,
    ),
    'test_minutes_ago' => 
    array (
      0 => 0.093674898147583,
      1 => 64,
    ),
    'test_seconds_from_now' => 
    array (
      0 => 0.058745861053467,
      1 => 64,
    ),
    'test_seconds_ago' => 
    array (
      0 => 0.054571866989136,
      1 => 64,
    ),
    'test_to_date' => 
    array (
      0 => 0.015947818756104,
      1 => 64,
    ),
    'test_ordinalize' => 
    array (
      0 => 0.080932855606079,
      1 => 64,
    ),
    'test_quantify' => 
    array (
      0 => 0.041258096694946,
      1 => 168,
    ),
    'test_until' => 
    array (
      0 => 0.032394886016846,
      1 => 64,
    ),
    'test_since' => 
    array (
      0 => 0.0091381072998047,
      1 => 64,
    ),
    'Test_db' => 
    array (
      0 => 0.04413890838623,
      1 => 1464,
    ),
    'Test_t' => 
    array (
      0 => 0.021781921386719,
      1 => 64,
    ),
    'Test_debug' => 
    array (
      0 => 0.013886213302612,
      1 => 64,
    ),
    'Test_get_object_info' => 
    array (
      0 => 0.017473936080933,
      1 => 64,
    ),
    'Test_get_this_object_methods' => 
    array (
      0 => 0.0091331005096436,
      1 => 64,
    ),
    'Test_get_this_object_attributes' => 
    array (
      0 => 0.011383056640625,
      1 => 64,
    ),
    'Test_for_StatusKeys' => 
    array (
      0 => 0.038531064987183,
      1 => 64,
    ),
    'test_should_increase_memory' => 
    array (
      0 => 0.008854866027832,
      1 => 122968,
    ),
    'test_should_not_increase_memory' => 
    array (
      0 => 0.018858194351196,
      1 => 64,
    ),
    'test_install_methods' => 
    array (
      0 => 0.3097620010376,
      1 => 493888,
    ),
    'test_remove_methods' => 
    array (
      0 => 0.33101511001587,
      1 => 64,
    ),
    'test_remove_repositories_config' => 
    array (
      0 => 0.029762983322144,
      1 => 210880,
    ),
    'test_should_get_available_repositories' => 
    array (
      0 => 0.015764951705933,
      1 => 288,
    ),
    'test_should_add_new_repository' => 
    array (
      0 => 0.030488014221191,
      1 => 232,
    ),
    'test_should_remove_repository' => 
    array (
      0 => 0.058334827423096,
      1 => -56,
    ),
  ),
); return $runtime[1398402206];