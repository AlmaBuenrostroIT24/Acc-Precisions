<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>NCAR {{ $ncar->ncar_no ?? $ncar->id }}</title>
    <style>
      body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
      h1 { font-size: 18px; margin: 0 0 10px; }
      .meta { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
      .meta td { padding: 4px 6px; vertical-align: top; }
      .label { color: #334155; font-weight: 700; width: 140px; }
      .box { border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; margin-top: 8px; }
      .muted { color: #475569; }
    </style>
  </head>
  <body>
    <h1>NCAR {{ $ncar->ncar_no ?? $ncar->id }}</h1>

    <table class="meta">
      <tr>
        <td class="label">Type</td>
        <td>{{ $ncar->type_name }}</td>
        <td class="label">Created</td>
        <td>{{ $ncar->created_at }}</td>
      </tr>
      <tr>
        <td class="label">Customer</td>
        <td>{{ $ncar->order_customer ?? $ncar->ncar_customer }}</td>
        <td class="label">Status</td>
        <td>{{ $ncar->status }}</td>
      </tr>
      <tr>
        <td class="label">Stage</td>
        <td>{{ $ncar->stage }}</td>
        <td class="label">Location</td>
        <td>{{ $ncar->location }}</td>
      </tr>
      <tr>
        <td class="label">Work ID</td>
        <td>{{ $ncar->work_id }}</td>
        <td class="label">CO / PO</td>
        <td>{{ $ncar->co }} <span class="muted">/</span> {{ $ncar->cust_po }}</td>
      </tr>
      <tr>
        <td class="label">PN</td>
        <td>{{ $ncar->PN }}</td>
        <td class="label">Ref</td>
        <td>{{ $ncar->ref }}</td>
      </tr>
    </table>

    <div class="box">
      <div class="label" style="width:auto; margin-bottom:4px;">Description</div>
      <div>{{ $ncar->nc_description }}</div>
    </div>

    <div class="box">
      <div class="label" style="width:auto; margin-bottom:4px;">Disposition</div>
      <div>{{ $ncar->disposition }}</div>
    </div>
  </body>
</html>
