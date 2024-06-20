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
        <div class="loading fixed-top bg-white" id="LoadingAnimation">
            <h2 class="mr-4 my-auto text-dark font-weight-normal">
                Please wait
            </h2>
            <div class="spinner-border text-dark" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <div class="container border border-light rounded shadow p-5 bg-white">
            <h1 class="text-center font-weight-bold">Jasa Pengiriman</h1>
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

                $.ajax({
                    url: "/cities",
                    method: "GET",
                    beforeSend: function () {
                        // Menampilkan animasi loading saat request dikirim
                        $("#LoadingAnimation").addClass(
                            "d-flex justify-content-center align-items-center"
                        );
                        $("#LoadingAnimation").removeClass("d-none");
                    },
                    success: function (data) {
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
                    },
                    error: function (xhr, status, error) {
                        console.error("Error:", xhr, status, error);
                        alert(
                            "Gagal memuat daftar kota. Silakan coba lagi nanti."
                        );
                    },
                    complete: function () {
                        // Menyembunyikan animasi loading setelah request selesai
                        $("#LoadingAnimation").addClass("d-none");
                        $("#LoadingAnimation").removeClass(
                            "d-flex justify-content-center align-items-center"
                        );
                    },
                });

                // Submit form
                $("#shippingForm").submit(function (event) {
                    event.preventDefault();

                    //Menonaktifkan submit btn dan mengaktifkan spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitBtnSpinner").removeClass("d-none");
                    $("#submitBtnText").text("Loading...");

                    var formData = {
                        origin: $("#origin").val(),
                        destination: $("#destination").val(),
                        weight: $("#weight").val(),
                        courier: $("#courier").val(),
                    };

                    $.post("/calculate-shipping", formData, function (data) {
                        var results =
                            "<h3 class='text-center font-weight-bold mt-5'>Total Ongkir</h3>";
                        results +=
                            '<table class="table table-striped mt-4"><thead><tr><th>Kurir</th><th>Servis</th><th>Biaya</th><th>Estimasi (Hari)</th></tr></thead><tbody>';

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

                        // Menghilangkan animasi loading pada submit btn
                        $("#submitBtn").prop("disabled", false);
                        $("#submitBtnSpinner").addClass("d-none");
                        $("#submitBtnText").text("Calculate Shipping Cost");
                    }).fail(function (xhr, status, error) {
                        // Handle failed request
                        console.error("Error:", xhr, status, error);
                        alert(
                            "Failed to calculate shipping cost. Please try again later."
                        );

                        // Menghilangkan animasi loading pada submit btn
                        $("#submitBtn").prop("disabled", false);
                        $("#submitBtnSpinner").addClass("d-none");
                        $("#submitBtnText").text("Calculate Shipping Cost");
                    });
                });
            });
        </script>
    </body>
</html>
