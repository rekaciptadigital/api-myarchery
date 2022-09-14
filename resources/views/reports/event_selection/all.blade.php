<!DOCTYPE html>
<html>
  <head>
    <title>Page Title</title>
    <style type="text/css" >
    div.page
    {
        page-break-after: always;
        page-break-inside: avoid;
		break-after:page;
		float:none;
    }
</style>
  </head>
  <body>
  	@foreach($pages as $page)
		<div class="page" style="break-after:page">
		{!! html_entity_decode($page) !!}
		</div>
	@endforeach 
  </body>
</html>