(function () {
	'use strict';
	document.addEventListener('click', function (e) {
		var add = e.target.closest('.khy-rep-add');
		var del = e.target.closest('.khy-rep-del');
		if (add) {
			var box = add.closest('.khy-repeater');
			var tpl = box.querySelector('.khy-rep-tpl');
			var rows = box.querySelector('.khy-rep-rows');
			var idx = Date.now().toString(36) + rows.children.length;
			var html = tpl.innerHTML.replace(/__i__/g, idx);
			var holder = document.createElement('div');
			holder.innerHTML = html.trim();
			rows.appendChild(holder.firstChild);
		}
		if (del) {
			var row = del.closest('.khy-rep-row');
			if (row) { row.remove(); }
		}
	});
})();
