
/**
 * Order by the <?php echo $columnName; ?> column
 */
public function orderBy<?php echo $columnPhpName; ?> ($order = Criteria::ASC)
{
    if ($order === Criteria::ASC) {
        return $this->addAscendingOrderByColumn('<?php echo $columnName; ?>');
    }

    return $this->addDescendingOrderByColumn('<?php echo $columnName; ?>');
}
