class GoogleMapAPI {
    constructor(dom, options) {
        google.maps.event.addDomListener(window, "load", this.initialize);
        this.initialize(dom, options);
    }

    initialize(dom, options){
        this.map = new google.maps.Map(dom, options);
    }

    static main() {
        let GoogleMap = new GoogleMapAPI(document.getElementById('app'), {
            center: {lat: 0, lng: 0},
            zoom: 0

        });
    }
}

GoogleMapAPI.main();
