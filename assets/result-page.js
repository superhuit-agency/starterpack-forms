(function() {
	document.querySelector('.button-delete').addEventListener('click', (event) => {
		const res = confirm('Are you sure you want to delete the form submissions ? This operation is irreversible.');

		if (! res) {
			event.preventDefault();
		}
	});

	const confirmTrash = function(event) {
		const res = confirm('Are you sure you want to trash this submission? This operation is irreversible.');
		if (! res) {
			event.preventDefault();
		}
	};

	Array.from(document.querySelectorAll('.trash-submission')).forEach((link) => {
		link.addEventListener('click', confirmTrash);
	});
}());
