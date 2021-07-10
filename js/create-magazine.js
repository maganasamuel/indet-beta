window.onload = function () {

	var api = "libs/api/magazine_api.php";
	var uploads_url = "../indet_photos_stash/";

	pages = [];

	function hasActivePage() {
		if ($('[name="page_radio"]:checked').length) {
			return true;
		}

		alert('Please select a page.');

		return false;
	}

	function addFabricImage(link) {
		fabric.Image.fromURL(link, function (image) {
			var index = $('[name="page_radio"]:checked').index('[name="page_radio"]');

			var space = (pages[index].getObjects().length + 1) * 15;

			var object = image.set({
				top: space,
				left: space,
			});

			object.scaleToHeight((pages[index].height / 2) * 0.75);
			object.scaleToWidth((pages[index].width / 2) * 0.75);

			pages[index].add(object);
		});
	}

	function testPage() {
		$('#addPage').trigger('click');

		$('[name="page_radio"]:eq(0)').prop('checked', true);

		addFabricImage(uploads_url + 'earls.jpg');

		setTimeout(() => {
			$('[name="page_radio"]:eq(0)').prop('checked', false);

			$('[name="page_radio"]:eq(1)').prop('checked', true);

			addFabricImage(uploads_url + 'shawarma.jpg');

			var object = pages[0].item(0);

			object.set({ top: 0, left: 0, scaleX: pages[0].width / object.width, scaleY: pages[0].height / object.height });

			pages[0].renderAll();

			setTimeout(() => {
				var object = pages[1].item(0);

				object.set({ top: 0, left: 0, scaleX: pages[1].width / object.width, scaleY: pages[1].height / object.height });

				pages[1].renderAll();
			}, 250);
		}, 250);
	}

	$(document).ready(function () {
		$('[data-toggle="popover"]').popover();

		$('.datepicker').datepicker({
			dateFormat: 'dd/mm/yy'
		});

		$("#date").on("change", function () {
			var date = $(this).val();
			var data = {
				date: date,
				action: "get_series"
			}

			$("#series").val("Loading...");

			$.ajax({
				dataType: 'json',
				type: 'POST',
				data: data,
				url: api,
				success: function (e) {
					$("#series").val(e.series);
					$("#magazine_date").val(e.date);
					$("#magazine_data").val(e.magazine_data);
				},
				error: function (x) {
					console.log(x);
				}
			});
		});

		[
			'Arial',
			'Verdana',
			'Helvetica',
			'Tahoma',
			'Trebuchet MS',
			'Times New Roman',
			'Georgia',
			'Garamond',
			'Courier New',
			'Brush Script MT',
		].forEach((font) => {
			$('#font').append(
				$('<option>', {
					value: font,
					text: font,
				})
			);
		});

		var width = 702;
		var height = 837;
		// var height = 97.2;

		$('#addPage').on('click', function () {
			var index = 1;

			if ($('[id^="page_"]').length) {
				index = ($('[id^="page_"]:last').data('index') - 0) + 1;
			}

			var canvas = $('<canvas>', {
				id: 'page_' + index,
				'data-index': index,
				class: 'page',
				width: width,
				height: height,
			});

			var tag = $('<label>', {
				class: 'page-label p-4 border rounded m-2 pointer'
			}).append(
				$('<input>', {
					type: 'radio',
					name: 'page_radio',
					hidden: true,
				})
			).append(
				$('<p>', {
					class: 'page-number small',
					text: 'Page ' + index,
				})
			).append(canvas);

			$('#pageWrapper').append(tag);

			var page = new fabric.Canvas('page_' + index, { width: width, height: height });

			page.id = 'page_' + index;

			page.renderAll();

			pages.push(page);

		}).click();

		// testPage();

		$(document).on('change', '[name="page_radio"]', function () {
			$('.page-label').removeClass('active');

			$(this).closest('.page-label').addClass('active');
		});

		$('#photos').on('click', function () {
			if (!hasActivePage()) {
				return false;
			}
		});

		$('#photos').on('change', function () {
			var files = $(this)[0].files;

			if (!files.length) {
				alert('Please select a file.');
				return;
			}

			var data = new FormData();

			$.each(files, function (index, file) {
				data.append("fileToUpload", file, file.name);

				$.ajax({
					url: "libs/api/uploader",
					type: 'POST',
					data,
					processData: false,
					contentType: false,
					success: function (response) {
						var link = uploads_url + response;

						addFabricImage(link);

						setTimeout(() => {
							$.ajax({
								url: 'libs/api/magazine_image_deleter',
								type: 'POST',
								data: {
									filename: response,
								},
								error: function (response, status, error) {
									console.log(response);
								}
							})
						}, 100);
					},
					error: function (response, status, error) {
						console.log(error);
						alert('An error occurred when uploading the file!');
					},
					complete: function () {
						$(this).val('');
					}
				});
			});
		});

		$('#photoText').on('keydown', function (event) {
			if (event.keyCode == 13) {
				$('#addText').trigger('click');
			}
		});

		$('#addText').on('click', function () {
			if (!hasActivePage()) {
				return false;
			}

			if (!$('#photoText').val().trim()) {
				alert('Please write a text to add in the photo.');

				$('#photoText').focus();

				return false;
			}

			var index = $('[name="page_radio"]:checked').index('[name="page_radio"]');

			var space = (pages[index].getObjects().length + 1) * 15;

			var text = new fabric.Text($('#photoText').val(), {
				top: space,
				left: space,
				fontFamily: $('#font').val(),
				fontWeight: $('#bold').is(':checked') ? 'bold' : 'normal',
				fontStyle: $('#italic').is(':checked') ? 'italic' : 'normal',
				underline: $('#underline').is(':checked') ? true : false,
				fill: $('#color').val(),
			});

			pages[index].add(text);

			pages[index].renderAll();
		});

		$('#removeObject').on('click', function () {
			if (!hasActivePage()) {
				return false;
			}

			var index = $('[name="page_radio"]:checked').index('[name="page_radio"]');

			var object = pages[index].getActiveObject();

			if (!object) {
				alert('Please select an image / text.');

				return false;
			}

			pages[index].remove(object);
		});

		$('#removePage').on('click', function () {
			if (!hasActivePage()) {
				return false;
			}

			var radio = $('[name="page_radio"]:checked');

			var index = radio.index('[name="page_radio"]');

			radio.closest('.page-label').remove();

			pages.splice(index, 1);

			$('.page-number').each(function (index, pageNumber) {
				$(pageNumber).text('Page ' + (index + 1));
			});

			return false;
		});

		$('#create').on('click', function (e) {
			e.preventDefault();

			var data = new FormData();
			var button = $(this);

			var date = $("#date").val();
			var quote = $("#quote").val();
			var message = $("#message").val();
			var announcement = $("#announcement").val();

			data.append("date", date);
			data.append("quote", quote);
			data.append("message", message);
			data.append("announcement", announcement);
			data.append("output", true);
			data.append("random_filename", true);

			Array.from(pages).forEach((page) => {
				if (!page.getObjects().length) {
					return true;
				}

				var dataUrl = page.toDataURL({
					format: 'png',
				});

				data.append('photos[]', dataUrl);
			});

			$.ajax({
				dataType: 'json',
				type: 'POST',
				data,
				processData: false,
				contentType: false,
				url: "generate_magazine.php",
				beforeSend: function () {
					button.prop("disabled", true);
					$("#create_magazine_text").text("Generating Report");
					$("#create_magazine_spinner").show();
				},
				success: function (e) {
					button.prop("disabled", false);
					$("#create_magazine_text").text("Create Magazine");
					$("#create_magazine_spinner").hide();

					var mydata = JSON.stringify(e.data);
					var link = e['link'];
					var htm = '<iframe src="' + link + '" style="width: 100%;height: 75%;"></iframe>';
					$('#myModal').modal('show');
					$('#myModal .modal-body').html(htm);
					$('#save_pdf').unbind("click");
					$('#save_pdf').on('click', function () {
						$.ajax({
							//dataType:'JSON',
							data: {
								magazine_data: mydata,
								action: "create_magazine"
							},
							type: 'POST',
							url: "libs/api/magazine_api.php",
							beforeSend: function () {
								$("#save_pdf").prop("disabled", true);
								$("#save_magazine_text").text("Saving Magazine");
								$("#save_magazine_spinner").show();
							},
							success: function (x) {
								$("#save_pdf").prop("disabled", false);
								$("#save_magazine_text").text("Create Magazine");
								$("#save_magazine_spinner").hide();

								$.confirm({
									title: 'Success!',
									content: 'You have successfully created a magazine ',
									buttons: {
										Ok: function () {
											window.location = 'create_magazine.php';
										},
									}
								});
							},
							error: function (x) {
								console.log(x);
								$("#save_pdf").prop("disabled", false);
								$("#save_magazine_text").text("Create Magazine");
								$("#save_magazine_spinner").hide();
							}
						});
					});
				},
				error: function (x) {
					console.log(x);
					button.prop("disabled", false);
					$("#create_magazine_text").text("Create Magazine");
					$("#create_magazine_spinner").hide();
				}
			});

		});

	});

}
