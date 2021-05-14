<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include_once 'libs/api/classes/general.class.php';

$_SESSION['x'] = 1;
unset($_SESSION['adviser_id']);

if (! isset($_SESSION['myusername'])) {
    session_destroy();
    header('Refresh:0; url=index.php');
}

if (! isset($_GET['adviser_id'])) {
    die('adviser_id query is required.');
}

if (! $_GET['adviser_id']) {
    die('adviser_id query is required.');
}

if (! is_numeric($_GET['adviser_id'])) {
    die('adviser_id query must be a number.');
}

require 'libs/api/classes/magazine.class.php';

$adviserController = new AdviserController();

$adviser = $adviserController->getAdviser($_GET['adviser_id']);

if (! $adviser) {
    die('adviser does not exist.');
}

$magazine = new Magazine(date('Ymd'), '', '', '', [], $adviser['id']);

$runs = $magazine->adviserRuns;

$levelClasses = [
    'Titanium' => 'bg-blue-500 text-white',
    'Platinum' => 'bg-red-500 text-white',
    'Gold' => 'bg-yellow-500 text-white',
    'Silver' => 'bg-gray-500 text-white',
	'none' => 'bg-white text-gray-900',
];

$uploadsFolder = '../indet_photos_stash/';
$defaultImagePath = '/images/default_pic.png';
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>INDENT</title>

	<?php include 'partials/nav_bar.html'; ?>

	<link href="https://fonts.googleapis.com/css?family=Roboto"
		rel="stylesheet">

	<link rel="stylesheet" href="styles.css">
	<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png"
		sizes="16x16">

	<link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css"
		rel="stylesheet">
</head>

<body style="font-family: Roboto;">
	<div class="jumbotron">
		<h2 class="slide text-center text-5xl">Adviser Strings</h2>
	</div>

	<div class="max-w-screen-lg mx-auto px-4 sm:px-6 md:px-8 mb-8">
		<div class="rounded-lg bg-white overflow-hidden shadow">
			<h2 class="sr-only" id="profile-overview-title">Adviser Strings
			</h2>
			<div class="bg-white p-6">
				<div class="sm:flex sm:items-center sm:justify-between">
					<div class="sm:flex sm:space-x-5">
						<div class="flex-shrink-0">
							<?php
							$src = $defaultImagePath;

							$imagePath = $uploadsFolder . $adviser['image'];

							if($adviser['image'] && file_exists($imagePath)){
								$src = 'https://onlineinsure.co.nz/indet_photos_stash/' . $adviser['image'];
							}

							?>
							<img class="mx-auto h-40 w-40 rounded-full"
								src="<?php echo $src; ?>"
								alt="">
						</div>
						<div
							class="mt-4 text-center sm:mt-0 sm:pt-1 sm:text-left">
							<p
								class="text-3xl font-bold text-gray-900 sm:text-4xl">
								<?php echo $adviser['name']; ?>
							</p>
							<p class="text-2xl font-medium text-gray-600">
								<?php echo $adviser['company_name']; ?>
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="bg-gray-50">
				<?php
                if ($runs) {
                    ?>
				<table class="table-auto w-full text-2xl">
					<thead class="text-gray-900">
						<tr>
							<th class="border border-gray-200 px-6 py-5 text-center"
								colspan="2">Period</th>
							<th class="border border-gray-200 px-6 py-5 text-center"
								rowspan="2">Policies</th>
							<th class="border border-gray-200 px-6 py-5 text-center"
								rowspan="2">APIs</th>
							<th class="border border-gray-200 px-6 py-5 text-center"
								rowspan="2">Score</th>
							<th class="border border-gray-200 px-6 py-5 text-center"
								rowspan="2">String</th>
						</tr>
						<tr>
							<th
								class="border border-gray-200 px-6 py-5 text-center">
								From</th>
							<th
								class="border border-gray-200 px-6 py-5 text-center">
								To</th>
						</tr>
					</thead>
					<tbody>
						<?php
                        foreach ($runs as $run) {
                            ?>
						<tr class="<?php echo $levelClasses[$run['level']]; ?>">
							<td class="border border-gray-200 px-6 py-3">
								<?php echo date('jS F, Y', strtotime($run['biMonthRange']->from)); ?>
							</td>
							<td class="border border-gray-200 px-6 py-3">
								<?php echo date('jS F, Y', strtotime($run['biMonthRange']->to)); ?>
							</td>
							<td
								class="border border-gray-200 px-6 py-3 text-right">
								<?php echo number_format($run['deals'] ?? 0); ?>
							</td>
							<td
								class="border border-gray-200 px-6 py-3 text-right">
								<?php echo number_format($run['issued_api'] ?? 0, 2); ?>
							</td>
							<td class="border border-gray-200 px-6 py-3 text-uppercase">
								<?php echo $run['level']; ?>
							</td>
							<td
								class="border border-gray-200 px-6 py-3 text-right">
								<?php echo number_format($run['string']); ?>
							</td>
						</tr>
						<?php
                        } ?>
					</tbody>
				</table>
				<?php
                } else {
                    ?>
				<p class="text-2xl px-6 py-5">No available strings.</p>
				<?php
                }
                ?>
			</div>
		</div>
	</div>
</body>

</html>
