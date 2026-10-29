<div class="no-gutter-xs-sm">
  <div class="ko-DataTableWrap">
<?= tag('table', $table_options, true); ?>
    <thead>
      <tr>
<?= $table->getTableHead(); ?>
      </tr>
    </thead>
    <tbody>
<?= $table->getTableBody(); ?>
    </tbody>
    </table>
  </div>
</div>

