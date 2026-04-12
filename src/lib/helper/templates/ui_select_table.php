<div class="no-gutter-xs-sm">
  <div class="uiTable">
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

