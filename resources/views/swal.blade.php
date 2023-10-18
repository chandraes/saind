@if (session('success'))
<script>
    Swal.fire(
            'Berhasil!',
            '{{session('success')}}',
            'success'
        )
</script>
@endif
@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '{{session('error')}}',
    })
</script>
@endif
