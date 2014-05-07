<?php
/**
./mrails doc:app                   # Build the app documetation Files into docs/app/api
./mrails doc:plugins               # Generate documation for all installed plugins in docs/plugins
./mrails doc:rails                # Build the rails documentation files into docs/rails/api
./mrails doc:website               # Add a new controller at /docs to browse avaliable documentation
./mrails doc:website:remove        # Removed the files added by ./mrails doc:website
*/

mrails_task('doc:rails', array(
'description' => 'Build the rails HTML Files'
));

mrails_task('doc:website', array(
'description' => 'Creates a website for browsing your docs at app/controllers/docs_controller.php'
));

mrails_task('doc:website:remove', array(
'description' => 'Removes the files added by ./mrails doc:website'
));

/*
mrails_task('doc:extract_metadata', array(
    'description' => 'Extracts metadata from source code files to generate the documentation'
));

*/