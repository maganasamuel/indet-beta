class DateHelper {
    constructor () {

    }

    convertDatestampToNZ(dateString){
        var year        = dateString.substring(0,4);
        var month       = dateString.substring(4,6);
        var day         = dateString.substring(6,8);

        var date        = day + "/" + month + "/" + year;
        return date;
    }
}