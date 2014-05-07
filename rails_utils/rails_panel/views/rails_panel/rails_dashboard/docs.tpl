<div class="main-content main_content_left">
<h1>_{Documentation}</h1>
<h2>_{PhpOnRails Framework documentation}</h2>
<ul>
    <li><%= link_to _('API'), :controller => 'rails_dashboard', :action => 'api', :module => 'rails_panel' %></li>
    <li><%= link_to _('Guides'), :controller => 'rails_dashboard', :action => 'guide', :module => 'rails_panel' %></li>
</ul>

<h2>_{<strong>%application_name</strong>} documentation</h2>
<ul>
    <li><%= link_to _('API'), :controller => 'docs', :action => 'app_api', :module => 'rails_panel' %></li>
</ul>

</div>