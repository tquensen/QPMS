<fieldset<?php if ($element->attributes): foreach ((array) $element->attributes as $attr => $attrValue): ?> <?php echo ' '.$attr.'="'.$attrValue.'"'; ?><?php endforeach; endif; ?><?php if ($element->class): ?> class="<?php echo $element->class; ?>"<?php endif; ?>><?php if($element->legend): ?><legend><?php echo htmlspecialchars($element->legend)?><?php if ($element->required && $element->getForm()->getOption('requiredMark')): ?><?php echo '<span class="requiredMark">'.htmlspecialchars($element->getForm()->getOption('requiredMark')).'</span>'; ?><?php endif; ?></legend><?php endif; ?>