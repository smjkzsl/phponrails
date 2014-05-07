<div id="header" class="no-print">
  <div class="top-buttons"> <a href="#" class="table-content-button" onclick="if($('table-content').visible()){new Effect.SlideUp($('table-content'));}else{new Effect.SlideDown($('table-content'))}"><span>_{Guides}</span></a>
  </div>
  <div id="search"> <span class="search-field">
    <input name="search-field" type="text" value="_{Find docs at rails.org}" onclick="if(this.value=='_{Find docs at rails.org}'){this.value='';}" />
    </span><a href="#" class="search-button"><span>_{Search}</span></a> </div>
  <div class="logo"><a href="http://www.rails.org/">Rails</a></div>
  <div class="application_name">{application_name}</div>
  <div id="top-nav">
    <ul>
        <%= get_menu_option _('Dashboard'),         :action => 'index' %>  
        <%= get_menu_option _('Web Terminal'),      :action => 'web_terminal' %>
        <%= get_menu_option _('Documentation'),     :action => 'docs' %>
        <hidden>
            <%= get_menu_option _('Plugins'),           _('http://www.rails.org/plugins') %>  
            <%= get_menu_option _('Screencasts'),       _('http://www.rails.org/screencasts') %>  
            <%= get_menu_option _('Get Help'),          _('http://www.rails.org/help') %>  
            <%= get_menu_option _('Contribute'),        _('http://www.rails.org/contribute') %>  
        </hidden>
      </ul>
  </div>
</div>
