var working = false;
$('.login').on('submit', function(e)
{
	e.preventDefault();
	if (working) return;
	working = true;
	var $this = $(this),
	$state = $this.find('button > .state');
	$this.addClass('loading');
	$state.html('Authenticating');
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			setTimeout(function()
			{
				if(xhr.responseText.length)
				{
					$this.addClass('ok');
					$state.html(xhr.responseText);
					setTimeout(function()
					{
						location.reload();
					}, 1500);
				}
				else location.reload();
			}, 1500);
			working = false;
		}
	};
	xhr.open("POST", "auth.php", true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send("u=" + document.getElementById("username").value + "&p=" + document.getElementById("password").value);
});
