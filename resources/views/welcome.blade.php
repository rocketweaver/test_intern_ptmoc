<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>RajaOngkir Shipping Calculator</title>
        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        />
        <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
    </head>
    <body class="bg-dark">
        <div class="container border border-light rounded shadow p-5 bg-white">
            <h1 class="text-center">Jasa Pengiriman</h1>
            <form
                id="shippingForm"
                class="mt-4"
                method="post"
                action="/calculate-shipping"
            >
                @csrf

                <div class="form-group">
                    <label for="origin">Kota Asal:</label>
                    <select
                        class="form-control"
                        id="origin"
                        name="origin"
                        required
                    >
                        <!-- Options akan muncul secara dinamis -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="destination">Kota Destinasi:</label>
                    <select
                        class="form-control"
                        id="destination"
                        name="destination"
                        required
                    >
                        <!-- Options akan muncul secara dinamis -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="weight">Berat (gram):</label>
                    <input
                        type="number"
                        id="weight"
                        class="form-control"
                        name="weight"
                        min="100"
                        required
                    />
                </div>
                <div class="form-group">
                    <label for="courier">Kurir:</label>
                    <select
                        class="form-control"
                        id="courier"
                        name="courier"
                        required
                    >
                        <option value="jne">JNE</option>
                        <option value="tiki">TIKI</option>
                        <option value="pos">Pos Indonesia</option>
                    </select>
                </div>

                <div class="form-group">
                    <button class="btn btn-primary w-100 py-2" type="submit">
                        <span
                            id="submitBtnSpinner"
                            class="spinner-border spinner-border-sm mr-2 d-none"
                            role="status"
                            aria-hidden="true"
                        ></span>
                        <span id="submitTxt">Submit</span>
                    </button>
                </div>
            </form>
            <div id="shippingCost" class="mt-5">
                <!-- Hasil perhitungan ongkir akan ditampilkan di sini -->
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            $(document).ready(function () {
                $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                });

                // Menampilkan kota destinasi dan asal
                $.get("/cities", function (data) {
                    var cities = data.rajaongkir.results;
                    if (Array.isArray(cities)) {
                        cities.forEach(function (city) {
                            $("#origin").append(
                                '<option value="' +
                                    city.city_id +
                                    '">' +
                                    city.city_name +
                                    "</option>"
                            );
                            $("#destination").append(
                                '<option value="' +
                                    city.city_id +
                                    '">' +
                                    city.city_name +
                                    "</option>"
                            );
                        });
                    } else {
                        $("#origin").append(
                            '<option value="' +
                                cities.city_id +
                                '">' +
                                cities.city_name +
                                "</option>"
                        );
                        $("#destination").append(
                            '<option value="' +
                                cities.city_id +
                                '">' +
                                cities.city_name +
                                "</option>"
                        );
                    }
                });

                // Submit form
                $("#shippingForm").submit(function (event) {
                    event.preventDefault();

                    // Disable submit button and show loading spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitBtnSpinner").removeClass("d-none");
                    $("#submitBtnText").text("Loading...");

                    var formData = {
                        origin: $("#origin").val(),
                        destination: $("#destination").val(),
                        weight: $("#weight").val(),
                        courier: $("#courier").val(),
                    };

                    // Logging formData to console
                    console.log("Form Data:", formData);

                    $.post("/calculate-shipping", formData, function (data) {
                        // Logging data to console
                        console.log("API Response:", data);

                        var results =
                            "<h3 class='text-center'>Total Ongkir</h3>";
                        results +=
                            '<table class="table table-striped mt-4"><thead><tr><th>Courier</th><th>Service</th><th>Cost</th><th>ETD (Days)</th></tr></thead><tbody>';

                        data.rajaongkir.results.forEach(function (service) {
                            service.costs.forEach(function (cost) {
                                results += "<tr>";
                                results += "<td>" + service.name + "</td>";
                                results += "<td>" + cost.service + "</td>";
                                results +=
                                    "<td>" + cost.cost[0].value + "</td>";
                                results += "<td>" + cost.cost[0].etd + "</td>";
                                results += "</tr>";
                            });
                        });

                        results += "</tbody></table>";
                        $("#shippingCost").html(results);

                        // Menambahkan animasi loading pada submit button
                        $("#submitBtn").prop("disabled", false);
                        $("#submitBtnSpinner").addClass("d-none");
                        $("#submitBtnText").text("Calculate Shipping Cost");
                    }).fail(function (xhr, status, error) {
                        // Handle failed request
                        console.error("Error:", xhr, status, error);
                        alert(
                            "Failed to calculate shipping cost. Please try again later."
                        );

                        // Menambahkan animasi loading pada submit button
                        $("#submitBtn").prop("disabled", false);
                        $("#submitBtnSpinner").addClass("d-none");
                        $("#submitBtnText").text("Calculate Shipping Cost");
                    });
                });
            });
        </script>
    </body>
</html>
