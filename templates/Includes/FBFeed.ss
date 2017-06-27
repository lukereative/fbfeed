<% loop $FBFeed %>
<article class="fb-post">
	<div class="post-time">
		<a href="$URL" target="_blank">$TimePosted.Format(d M Y)</a><br>
		<a href="$URL" target="_blank" class="post-full-link" title="View full post on Facebook"></a>
	</div>
	<div class="post-content">
		<% if $ImageSource %>
		<div class="post-image">
			<a href="$URL" target="_blank"><img src="$ImageSource"></a>
		</div>
		<% end_if %>
		$PostSummary
	</div>
</article>
<% end_loop %>
