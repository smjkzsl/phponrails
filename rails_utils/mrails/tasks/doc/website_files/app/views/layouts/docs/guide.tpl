<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title>{?page_title}{_page_title}{else}_{Rails guides}{end}</title>

<%= stylesheet_link_tag 'docs/guide/style.css' %>
<%= stylesheet_link_tag 'docs/guide/syntax.css' %>
<%= stylesheet_link_tag 'docs/guide/print.css', :media => 'print' %>

<%= javascript_include_tag 'docs/guide/guides.js' %>
<%= javascript_include_tag 'docs/guide/code_highlighter.js' %>
<%= javascript_include_tag 'docs/guide/highlighters.js' %>

</head>
<body class="guide">
  <div id="header">
    <div class="wrapper clearfix">
      <h1><%= link_to_guide 'Guides Index' %></h1>
      <p class="hide"><a href="#mainCol">Skip navigation</a>.</p>
      <ul class="nav">
        <li><a href="index.html">Home</a></li>
        <li class="index">
          <%= link_to_guide 'Guides Index', 'getting_started', :onclick=>"guideMenu(); return false;", :id=>"guidesMenu" %>
          <div id="guides" class="clearfix" style="display: none;">
            <hr />
            <dl class="L">
              <dt>Start Here</dt>
              <dd><%= link_to_guide 'Getting Started with Rails', 'getting_started' %></dd>
              <dt>Models</dt>
              <dd><%= link_to_guide 'Rails Database Migrations', 'migrations' %></dd>
              <dd><%= link_to_guide 'Active Record Validations and Callbacks', 'activerecord_validations_callbacks' %></dd>
              <dd><%= link_to_guide 'Active Record Associations', 'association_basics' %></dd>
              <dd><%= link_to_guide 'Active Record Query Interface', 'active_record_querying' %></dd>
              <dt>Views</dt>
              <dd><%= link_to_guide 'Layouts and Rendering in Rails', 'layouts_and_rendering' %></dd>
              <dd><%= link_to_guide 'Action View Form Helpers', 'form_helpers' %></dd>
              <dt>Controllers</dt>
              <dd><%= link_to_guide 'Action Controller Overview', 'action_controller_overview' %></dd>
              <dd><%= link_to_guide 'Rails Routing from the Outside In', 'routing' %></dd>
            </dl>
            <dl class="R">
              <dt>Digging Deeper</dt>
              <dd><%= link_to_guide 'Rails Internationalization API', 'i18n' %></dd>
              <dd><%= link_to_guide 'Action Mailer Basics', 'action_mailer_basics' %></dd>
              <dd><%= link_to_guide 'Testing Rails Applications', 'testing' %></dd>
              <dd><%= link_to_guide 'Securing Rails Applications', 'security' %></dd>
              <dd><%= link_to_guide 'Debugging Rails Applications', 'debugging_rails_applications' %></dd>
              <dd><%= link_to_guide 'Performance Testing Rails Applications', 'performance_testing' %></dd>
              <dd><%= link_to_guide 'The Basics of Creating Rails Plugins', 'plugins' %></dd>
              <dd><%= link_to_guide 'Configuring Rails Applications', 'configuring' %></dd>
              <dd><%= link_to_guide 'Rails Command Line Tools and Mrails Tasks', 'command_line' %></dd>
              <dd><%= link_to_guide 'Caching with Rails', 'caching_with_rails' %></dd>
              <dd><%= link_to_guide 'Contributing to Rails', 'contributing_to_rails' %></dd>
            </dl>
          </div>
        </li>
      </ul>
    </div>
  </div>
  <hr class="hide" />

  <div id="feature">
    <div class="wrapper">
      {header_section?}
      {index_section?}
    </div>
  </div>

  <div id="container">
    <div class="wrapper">
      <div id="mainCol">
        {content_for_layout}
      </div>
    </div>
  </div>

  <hr class="hide" />
  <div id="footer">
    <div class="wrapper">
      <p>_{This work is an adaptation from the <a href="http://guides.rubyonrails.org/credits.html">Ruby on Rails guides</a> to <a href="http://www.rails.org/">Rails</a>}</p>
      <p>_{This work is licensed under a <a href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution-Share Alike 3.0</a> License</a>}</p>
    </div>
  </div>
</body>
</html>
