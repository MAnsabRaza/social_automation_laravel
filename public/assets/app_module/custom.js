const today = new Date();
const formattedDate = today.toISOString().split('T')[0];
$('.ts_datepicker').val(formattedDate);
