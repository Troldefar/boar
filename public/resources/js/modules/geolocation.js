export default {
    init: function() {
        // window.navigator.geolocation.getCurrentPosition(function(res) {
        //     console.log(res);
        // });

        window.addEventListener("devicemotion", function(event) {
            const x = event.accelerationIncludingGravity.x;
            const y = event.accelerationIncludingGravity.y;
            const z = event.accelerationIncludingGravity.z;
            const acceleration = event.acceleration;
            const rotation = event.rotationRate;
            console.log(`
                x: ${x} -
                y: ${y} -
                z: ${z} -
                acceleration: ${acceleration} -
                rotation: ${rotation} -
            `);
        });
    },
    promptLocationPermissions: function() {
        window.navigator.geolocation.getCurrentPosition(function(res) {
            return res;
        });
    }
}