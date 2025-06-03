class Fondo {
    constructor() {
        this.apiKey = "6f0341683ea3275017c15c42e61ea7a5"; // Tu API Key de Flickr
    }

    getImageById(photoId) {
        const url = "https://api.flickr.com/services/rest/";

        $.ajax({
            url: url,
            method: "GET",
            dataType: "json",
            data: {
                method: "flickr.photos.getInfo",
                api_key: this.apiKey,
                photo_id: photoId,
                format: "json",
                nojsoncallback: 1
            },
            success: function (response) {
                if (response.photo) {
                    const farm = response.photo.farm;
                    const server = response.photo.server;
                    const id = response.photo.id;
                    const secret = response.photo.secret;
                    const imageUrl = `https://farm${farm}.staticflickr.com/${server}/${id}_${secret}_b.jpg`;
                    $("body").css({
                        "background-image": `url(${imageUrl})`,
                        "background-size": "cover",
                        "background-repeat": "no-repeat",
                        "background-attachment": "fixed",
                        "background-position": "center"
                    });
                } else {
                    console.log("No se encontró información para esta foto.");
                }
            },
            error: function (error) {
                console.error("Error al obtener la imagen:", error);
            }
        });
    }
}