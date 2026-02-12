function macheRequest(url, id) {
	fetch(url, { method: 'GET', credentials: 'same-origin' })
		.then(function (res) {
			if (!res.ok) {
				throw new Error('HTTP ' + res.status);
			}
			return res.text();
		})
		.then(function (html) {
			var myElement = document.getElementById(id);
			if (myElement != null) {
				myElement.innerHTML = html;
			}
		})
		.catch(function () {
			alert('Bei dem Request ist ein Problem aufgetreten.');
		});
	return true;
}

function alertInhalt2(id) {
	return id;
}
